<?php

namespace App\Http\Controllers;

use App\Models\SettlementPeriod;
use App\Models\UserPeriodResult;
use App\Services\RankingService;

class ApiPeriodController extends Controller
{
    public function ranking(SettlementPeriod $period, RankingService $rankingService)
    {
        return response()->json([
            'rows' => $rankingService->calculatePeriodRanking($period)->map(fn ($row) => [
                'rank' => $row->rank,
                'name' => $row->user->full_name,
                'points' => $row->total_points,
                'entries' => $row->total_entries,
            ]),
        ]);
    }

    public function settlement(SettlementPeriod $period)
    {
        $row = UserPeriodResult::where('period_id', $period->id)->where('user_id', auth()->id())->first();

        return response()->json([
            'summary' => $row ? [
                'total_paid_amount' => $row->total_paid_amount,
                'total_points' => $row->total_points,
                'rank' => $row->rank,
                'reward_amount' => $row->reward_amount,
                'referral_bonus_amount' => $row->referral_bonus_amount,
                'final_settlement_amount' => $row->final_settlement_amount,
                'settlement_status' => $row->settlement_status,
            ] : null,
        ]);
    }
}
