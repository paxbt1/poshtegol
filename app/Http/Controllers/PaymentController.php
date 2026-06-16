<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\PredictionEntry;
use App\Services\MatchLockService;
use App\Services\PaymentAmountService;
use App\Services\PaymentGatewaySettings;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use Throwable;

class PaymentController extends Controller
{
    public function pay(PredictionEntry $entry, Request $request, PaymentAmountService $amountService, MatchLockService $lockService, PaymentGatewaySettings $gatewaySettings)
    {
        $gatewaySettings->apply();
        abort_unless($entry->user_id === $request->user()->id, 403);
        $entry->load('match');

        if ($entry->payment_status === 'pending') {
            $pendingTransaction = $entry->transactions()
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($pendingTransaction?->transaction_id) {
                return response()->json([
                    'message' => 'در حال انتقال به درگاه پرداخت...',
                    'redirect' => rtrim((string) config('payment.drivers.zibal.apiPaymentUrl'), '/').'/'.$pendingTransaction->transaction_id,
                ]);
            }

            if ($pendingTransaction) {
                $pendingTransaction->update([
                    'status' => 'failed',
                    'callback_payload' => ['error' => 'Payment request did not reach gateway. Reset before retry.'],
                ]);

                $entry->update([
                    'payment_status' => 'unpaid',
                    'prediction_status' => 'draft',
                ]);

                $entry->refresh();
            }
        }

        if (! in_array($entry->payment_status, ['unpaid', 'failed'], true)) {
            throw ValidationException::withMessages(['payment' => 'این پیش‌بینی قبلا وارد فرایند پرداخت شده است.']);
        }

        if (! $lockService->canPredict($entry->match)) {
            throw ValidationException::withMessages(['payment' => 'زمان پیش‌بینی این بازی به پایان رسیده است.']);
        }

        if (config('payment.default', 'zibal') === 'zibal' && blank(config('payment.drivers.zibal.merchantId'))) {
            throw ValidationException::withMessages(['payment' => 'مرچنت‌کد زیبال تنظیم نشده است. در حالت غیر Sandbox باید ZIBAL_MERCHANT_ID یا مقدار تنظیمات درگاه را ثبت کنید.']);
        }

        $transaction = PaymentTransaction::create([
            'user_id' => $request->user()->id,
            'prediction_entry_id' => $entry->id,
            'gateway' => config('payment.default', 'zibal'),
            'amount' => $entry->payable_amount,
            'amount_gateway' => $amountService->toGateway($entry->payable_amount),
            'entry_amount' => $entry->entry_amount,
            'gateway_fee_amount' => $entry->gateway_fee_amount,
            'status' => 'pending',
            'request_payload' => [
                'entry_id' => $entry->id,
                'match_id' => $entry->match_id,
                'gateway_description' => 'درخواست پرداخت',
            ],
        ]);

        $entry->update([
            'payment_status' => 'pending',
            'prediction_status' => 'pending_payment',
        ]);

        $invoice = (new Invoice())
            ->amount($transaction->amount_gateway)
            ->detail([
                'mobile' => $request->user()->mobile,
                'description' => 'درخواست پرداخت',
                'orderId' => 'prediction-'.$entry->id.'-'.$transaction->id,
            ]);

        try {
            $redirection = Payment::via($transaction->gateway)
                ->callbackUrl(route('payment.callback.zibal', ['transaction' => $transaction->id]))
                ->purchase($invoice, function ($driver, $transactionId) use ($transaction) {
                    $transaction->update(['transaction_id' => (string) $transactionId]);
                })
                ->pay();
        } catch (Throwable $e) {
            Log::warning('payment purchase failed', [
                'gateway' => $transaction->gateway,
                'transaction_id' => $transaction->id,
                'prediction_entry_id' => $entry->id,
                'amount_gateway' => $transaction->amount_gateway,
                'merchant_id_present' => filled(config('payment.drivers.zibal.merchantId')),
                'callback_url' => config('payment.drivers.zibal.callbackUrl'),
                'currency' => config('payment.drivers.zibal.currency'),
                'exception_class' => $e::class,
                'exception_code' => $e->getCode(),
                'error' => $e->getMessage(),
            ]);

            $transaction->update([
                'status' => 'failed',
                'callback_payload' => [
                    'error' => $e->getMessage(),
                    'exception_class' => $e::class,
                    'exception_code' => $e->getCode(),
                ],
            ]);

            $entry->update([
                'payment_status' => 'unpaid',
                'prediction_status' => 'draft',
            ]);

            throw ValidationException::withMessages([
                'payment' => $this->purchaseErrorMessage($e),
            ]);
        }

        return response()->json([
            'message' => 'در حال انتقال به درگاه پرداخت...',
            'redirect' => $redirection->getAction(),
        ]);
    }

    public function callback(Request $request, MatchLockService $lockService, PredictionService $predictionService, PaymentGatewaySettings $gatewaySettings)
    {
        $gatewaySettings->apply();
        $transaction = PaymentTransaction::findOrFail($request->integer('transaction'));

        if (in_array($transaction->status, ['paid', 'needs_review'], true)) {
            return redirect()->route('payment.result', $transaction);
        }

        $trackId = $request->input('trackId') ?: $request->input('transactionId') ?: $request->input('trasactionId') ?: $transaction->transaction_id;

        try {
            $receipt = Payment::via($transaction->gateway)
                ->amount($transaction->amount_gateway)
                ->transactionId($trackId)
                ->verify();

            DB::transaction(function () use ($transaction, $request, $receipt, $trackId, $lockService, $predictionService) {
                $entry = $transaction->predictionEntry()->with('match')->lockForUpdate()->firstOrFail();
                $isLocked = ! $lockService->canPredict($entry->match);

                $transaction->update([
                    'transaction_id' => (string) $trackId,
                    'reference_id' => method_exists($receipt, 'getReferenceId') ? (string) $receipt->getReferenceId() : null,
                    'status' => $isLocked ? 'needs_review' : 'paid',
                    'callback_payload' => $request->all(),
                    'paid_at' => now(),
                    'verified_at' => now(),
                ]);

                if ($isLocked) {
                    $entry->update([
                        'payment_status' => 'needs_review',
                        'prediction_status' => 'needs_review',
                        'paid_at' => now(),
                    ]);
                } else {
                    $predictionService->finalizeAfterPayment($entry);
                }
            });
        } catch (Throwable $e) {
            $transaction->update([
                'status' => 'failed',
                'callback_payload' => array_merge($request->all(), ['error' => $e->getMessage()]),
            ]);
            $transaction->predictionEntry?->update([
                'payment_status' => 'failed',
                'prediction_status' => 'draft',
            ]);
        }

        return redirect()->route('payment.result', $transaction);
    }

    public function result(PaymentTransaction $transaction)
    {
        abort_unless(auth()->user()?->is_admin || $transaction->user_id === auth()->id(), 403);

        return view('payment.result', [
            'transaction' => $transaction->load(['predictionEntry.match.homeTeam', 'predictionEntry.match.awayTeam', 'predictionEntry.qualifiedTeam']),
        ]);
    }

    private function purchaseErrorMessage(Throwable $e): string
    {
        $code = (int) $e->getCode();
        $known = [
            102 => 'مرچنت زیبال پیدا نشد.',
            103 => 'مرچنت زیبال غیرفعال است.',
            104 => 'مرچنت‌کد زیبال نامعتبر است.',
            105 => 'مبلغ پرداخت باید بیشتر از ۱۰۰۰ ریال باشد.',
            106 => 'Callback URL زیبال نامعتبر است.',
            113 => 'مبلغ تراکنش از سقف مجاز مرچنت بیشتر است.',
        ];

        if (isset($known[$code])) {
            return 'خطا در اتصال به درگاه زیبال: '.$known[$code].' کد '.$code;
        }

        $message = trim($e->getMessage());
        if ($message === '' || str_contains($message, 'ناشناخته')) {
            return 'خطا در اتصال به درگاه زیبال. کد خطا: '.($code ?: 'نامشخص').'. جزئیات در storage/logs/laravel.log ثبت شد.';
        }

        return 'خطا در اتصال به درگاه زیبال: '.$message;
    }
}
