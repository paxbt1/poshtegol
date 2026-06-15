<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\PredictionEntry;
use App\Services\LivePredictionStatusService;
use App\Services\RankingService;

class LiveController extends Controller
{
    public function show(FootballMatch $match, LivePredictionStatusService $statusService, RankingService $rankingService)
    {
        $match->load(['homeTeam', 'awayTeam', 'events.team', 'period']);
        $prediction = PredictionEntry::with('match')
            ->where('user_id', auth()->id())
            ->where('match_id', $match->id)
            ->where('payment_status', 'paid')
            ->first();

        $ranking = $match->period ? $rankingService->calculatePeriodRanking($match->period)->take(5) : collect();

        return view('live.show', [
            'match' => $match,
            'prediction' => $prediction,
            'predictionStatus' => $prediction ? $statusService->evaluate($prediction) : null,
            'ranking' => $ranking,
        ]);
    }

    public function status(FootballMatch $match, LivePredictionStatusService $statusService, RankingService $rankingService)
    {
        $match->load(['homeTeam', 'awayTeam', 'events.team', 'period']);
        $prediction = PredictionEntry::with('match')
            ->where('user_id', auth()->id())
            ->where('match_id', $match->id)
            ->where('payment_status', 'paid')
            ->first();

        return response()->json([
            'score' => ($match->home_score ?? 0).' - '.($match->away_score ?? 0),
            'status' => $match->status,
            'minute' => $match->minute,
            'prediction_status' => $prediction ? $statusService->evaluate($prediction) : null,
            'events' => $match->events->map(fn ($event) => [
                'minute' => $event->minute,
                'title' => $event->title,
                'description' => $event->description,
                'team' => $event->team?->name_fa,
            ]),
            'ranking' => $match->period ? $rankingService->calculatePeriodRanking($match->period)->take(5)->values() : [],
        ]);
    }

    public function dashboardSummary()
    {
        $liveMatches = FootballMatch::with(['homeTeam', 'awayTeam'])
            ->whereIn('status', ['live_first_half', 'halftime', 'live_second_half'])
            ->orderBy('starts_at')
            ->get();

        return response()->json([
            'live_matches_count' => $liveMatches->count(),
            'live_matches' => $liveMatches->map(fn ($match) => [
                'id' => $match->id,
                'title' => ($match->homeTeam?->name_fa ?? $match->bracket_slot_home ?? 'تیم میزبان').' - '.($match->awayTeam?->name_fa ?? $match->bracket_slot_away ?? 'تیم مهمان'),
                'score' => ($match->home_score ?? 0).' - '.($match->away_score ?? 0),
                'url' => route('live.show', $match),
            ]),
        ]);
    }
}
