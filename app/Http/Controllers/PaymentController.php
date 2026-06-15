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

        if ($entry->payment_status !== 'unpaid') {
            throw ValidationException::withMessages(['payment' => 'این پیش‌بینی قبلا وارد فرایند پرداخت شده است.']);
        }

        if (! $lockService->canPredict($entry->match)) {
            throw ValidationException::withMessages(['payment' => 'زمان پیش‌بینی این بازی به پایان رسیده است.']);
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
            'request_payload' => ['entry_id' => $entry->id, 'match_id' => $entry->match_id],
        ]);

        $entry->update([
            'payment_status' => 'pending',
            'prediction_status' => 'pending_payment',
        ]);

        $invoice = (new Invoice())
            ->amount($transaction->amount_gateway)
            ->detail([
                'mobile' => $request->user()->mobile,
                'description' => 'پرداخت پیش‌بینی کاپ خانوادگی',
                'orderId' => 'prediction-'.$entry->id.'-'.$transaction->id,
            ]);

        $redirection = Payment::via($transaction->gateway)
            ->callbackUrl(route('payment.callback.zibal', ['transaction' => $transaction->id]))
            ->purchase($invoice, function ($driver, $transactionId) use ($transaction) {
                $transaction->update(['transaction_id' => (string) $transactionId]);
            })
            ->pay();

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
}
