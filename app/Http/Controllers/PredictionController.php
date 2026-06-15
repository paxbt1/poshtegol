<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePredictionRequest;
use App\Models\FootballMatch;
use App\Services\MatchLockService;
use App\Services\PaymentAmountService;
use App\Services\PredictionService;

class PredictionController extends Controller
{
    public function preview(FootballMatch $match, MatchLockService $lockService, PredictionService $predictionService, PaymentAmountService $amountService)
    {
        $amounts = $predictionService->calculateAmounts($match);
        $canPredict = $lockService->canPredict($match);

        return response()->json([
            'message' => $canPredict ? 'خلاصه پرداخت به‌روزرسانی شد.' : $lockService->reason($match),
            'can_predict' => $canPredict,
            'lock_reason' => $lockService->reason($match),
            'entry_amount' => $amounts['entry_amount'],
            'payable_amount' => $amounts['payable_amount'],
            'entry_amount_label' => $amountService->format($amounts['entry_amount']),
            'payable_amount_label' => $amountService->format($amounts['payable_amount']),
        ]);
    }

    public function store(StorePredictionRequest $request, FootballMatch $match, PredictionService $predictionService, PaymentAmountService $amountService)
    {
        $entry = $predictionService->createOrUpdateDraftPrediction($request->user(), $match, $request->validated());

        return response()->json([
            'message' => 'پیش‌بینی ثبت شد. برای فعال شدن، پرداخت را انجام دهید.',
            'entry_id' => $entry->id,
            'pay_url' => route('predictions.pay', $entry),
            'entry_amount_label' => $amountService->format($entry->entry_amount),
            'payable_amount_label' => $amountService->format($entry->payable_amount),
        ]);
    }
}
