<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePredictionRequest;
use App\Models\FootballMatch;
use App\Services\MatchLockService;
use App\Services\PredictionService;

class PredictionController extends Controller
{
    public function preview(FootballMatch $match, MatchLockService $lockService, PredictionService $predictionService)
    {
        $stakeTokens = max(50, (int) request('stake_tokens', 50));
        $amounts = $predictionService->calculateAmounts($match, $stakeTokens);
        $canPredict = $lockService->canPredict($match);

        return response()->json([
            'message' => $canPredict ? 'خلاصه توکن به‌روزرسانی شد.' : $lockService->reason($match),
            'can_predict' => $canPredict,
            'lock_reason' => $lockService->reason($match),
            'entry_amount' => $amounts['entry_amount'],
            'payable_amount' => $amounts['payable_amount'],
            'entry_amount_label' => number_format($amounts['entry_amount']).' توکن',
            'payable_amount_label' => number_format($amounts['payable_amount']).' توکن',
        ]);
    }

    public function store(StorePredictionRequest $request, FootballMatch $match, PredictionService $predictionService)
    {
        $entry = $predictionService->createOrUpdateDraftPrediction($request->user(), $match, $request->validated());

        return response()->json([
            'message' => 'پیش‌بینی با '.number_format($entry->entry_amount).' توکن ثبت و قفل شد.',
            'entry_id' => $entry->id,
            'entry_amount_label' => number_format($entry->entry_amount).' توکن',
            'payable_amount_label' => number_format($entry->payable_amount).' توکن',
            'redirect' => route('matches.show', $match),
        ]);
    }
}
