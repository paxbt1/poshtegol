<?php

namespace App\Services;

use App\Models\PredictionEntry;
use App\Models\SettlementPeriod;
use App\Models\UserPeriodResult;

class RankingService
{
    public function calculatePeriodRanking(SettlementPeriod $period)
    {
        $rows = PredictionEntry::query()
            ->with('result')
            ->where('period_id', $period->id)
            ->where('payment_status', 'paid')
            ->where('prediction_status', 'locked')
            ->get()
            ->groupBy('user_id')
            ->map(function ($entries, $userId) use ($period) {
                $firstPaidAt = $entries->min('paid_at') ?? $entries->min('created_at');
                $result = UserPeriodResult::updateOrCreate(
                    ['period_id' => $period->id, 'user_id' => $userId],
                    [
                        'total_entries' => $entries->count(),
                        'total_entry_amount' => $entries->sum('entry_amount'),
                        'total_paid_amount' => $entries->sum('payable_amount'),
                        'total_points' => $entries->sum(fn ($entry) => (int) ($entry->result?->total_points ?? 0)),
                    ],
                );

                return [
                    'model' => $result,
                    'first_paid_at_for_sort' => $firstPaidAt,
                    'total_points' => $result->total_points,
                ];
            })
            ->sortBy(fn (array $row) => $row['first_paid_at_for_sort'])
            ->sortByDesc(fn (array $row) => $row['total_points'])
            ->values();

        $rank = 1;
        $rows->each(function (array $row) use (&$rank) {
            $row['model']->update(['rank' => $rank++]);
        });

        return UserPeriodResult::with('user')
            ->where('period_id', $period->id)
            ->orderByRaw('rank is null, rank asc')
            ->get();
    }
}
