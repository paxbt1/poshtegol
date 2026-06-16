<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\PaymentTransaction;
use App\Models\PredictionEntry;
use App\Services\MatchLockService;
use App\Services\PaymentAmountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function pay(PredictionEntry $entry, Request $request, PaymentAmountService $amountService, MatchLockService $lockService)
    {
        abort_unless($entry->user_id === $request->user()->id, 403);
        $entry->load('match');

        if ($entry->payment_status === 'pending') {
            $entry->transactions()
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'callback_payload' => ['error' => 'Cancelled while switching to offline card payment.'],
                ]);

            $entry->update([
                'payment_status' => 'unpaid',
                'prediction_status' => 'draft',
            ]);

            $entry->refresh();
        }

        if ($entry->payment_status === 'pending_review') {
            throw ValidationException::withMessages(['payment' => 'رسید این پیش‌بینی قبلا ثبت شده و در انتظار تایید مدیر است.']);
        }

        if (! in_array($entry->payment_status, ['unpaid', 'failed'], true)) {
            throw ValidationException::withMessages(['payment' => 'این پیش‌بینی قبلا وارد فرایند پرداخت شده است.']);
        }

        if (! $lockService->canPredict($entry->match)) {
            throw ValidationException::withMessages(['payment' => $lockService->reason($entry->match)]);
        }

        $data = $request->validate([
            'payer_card_number' => ['required', 'regex:/^\d{16}$/'],
            'receipt_number' => ['required', 'string', 'max:100'],
        ], [], [
            'payer_card_number' => 'شماره کارت واریزکننده',
            'receipt_number' => 'شماره رسید تراکنش',
        ]);

        $destinationCard = AppSetting::getValue('offline_payment_card_number', '6221061063729273');

        $transaction = DB::transaction(function () use ($entry, $request, $amountService, $data, $destinationCard) {
            $entry->transactions()
                ->whereIn('status', ['pending', 'pending_review', 'failed'])
                ->update(['status' => 'cancelled']);

            $transaction = PaymentTransaction::create([
                'user_id' => $request->user()->id,
                'prediction_entry_id' => $entry->id,
                'gateway' => 'offline_card',
                'amount' => $entry->payable_amount,
                'amount_gateway' => $entry->payable_amount,
                'entry_amount' => $entry->entry_amount,
                'gateway_fee_amount' => 0,
                'reference_id' => trim($data['receipt_number']),
                'status' => 'pending_review',
                'request_payload' => [
                    'method' => 'card_to_card',
                    'destination_card_number' => $destinationCard,
                    'payer_card_number' => preg_replace('/\D+/', '', $data['payer_card_number']),
                    'receipt_number' => trim($data['receipt_number']),
                    'amount_label' => $amountService->format($entry->payable_amount),
                ],
            ]);

            $entry->update([
                'payment_status' => 'pending_review',
                'prediction_status' => 'pending_review',
            ]);

            return $transaction;
        });

        return response()->json([
            'message' => 'رسید پرداخت ثبت شد و در انتظار تایید مدیر است.',
            'redirect' => route('payment.result', $transaction),
        ]);
    }

    public function result(PaymentTransaction $transaction)
    {
        abort_unless(auth()->user()?->is_admin || $transaction->user_id === auth()->id(), 403);

        return view('payment.result', [
            'transaction' => $transaction->load(['predictionEntry.match.homeTeam', 'predictionEntry.match.awayTeam', 'predictionEntry.qualifiedTeam']),
        ]);
    }
}
