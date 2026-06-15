<?php

namespace App\Http\Controllers;

use App\Models\SettlementPeriod;
use App\Models\UserPeriodResult;
use App\Services\RankingService;

class RankingController extends Controller
{
    public function index(RankingService $rankingService)
    {
        $periods = SettlementPeriod::query()->orderBy('id')->get();
        $activePeriod = $periods->first();

        if ($activePeriod) {
            $rankingService->calculatePeriodRanking($activePeriod);
        }

        return view('ranking.index', [
            'periods' => $periods,
            'activePeriod' => $activePeriod,
            'rows' => $activePeriod ? UserPeriodResult::with('user')->where('period_id', $activePeriod->id)->orderBy('rank')->get() : collect(),
            'myResult' => $activePeriod ? UserPeriodResult::where('period_id', $activePeriod->id)->where('user_id', auth()->id())->first() : null,
        ]);
    }

    public function settlements(RankingService $rankingService)
    {
        return $this->index($rankingService);
    }
}
