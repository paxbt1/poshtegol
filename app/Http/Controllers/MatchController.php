<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Services\MatchLockService;
use App\Services\PredictionService;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $query = FootballMatch::query()->with(['homeTeam', 'awayTeam', 'period'])->orderBy('starts_at');

        if ($stage = $request->query('stage')) {
            if ($stage === 'knockout') {
                $query->whereIn('stage', ['round_32', 'round_16', 'quarter_final', 'semi_final', 'bronze_final']);
            } elseif ($stage === 'final') {
                $query->where('stage', 'final');
            } else {
                $query->where('stage', $stage);
            }
        }

        if ($group = $request->query('group')) {
            $query->where('group_name', $group);
        }

        if ($time = $request->query('time')) {
            match ($time) {
                'today' => $query->whereDate('starts_at', today()),
                'tomorrow' => $query->whereDate('starts_at', today()->addDay()),
                'live' => $query->whereIn('status', ['live', 'live_first_half', 'halftime', 'live_second_half']),
                'finished' => $query->where('status', 'finished'),
                default => null,
            };
        }

        return view('matches.index', ['matches' => $query->get()]);
    }

    public function show(FootballMatch $match, MatchLockService $lockService, PredictionService $predictionService)
    {
        $match->load([
            'homeTeam',
            'awayTeam',
            'period',
            'predictionEntries' => fn ($query) => $query
                ->whereIn('payment_status', ['paid', 'paid_but_locked'])
                ->with(['user', 'result'])
                ->latest('paid_at'),
        ]);

        return view('matches.show', [
            'match' => $match,
            'canPredict' => $lockService->canPredict($match),
            'lockReason' => $lockService->reason($match),
            'paidEntry' => $predictionService->paidEntry(auth()->user(), $match),
            'amounts' => $predictionService->calculateAmounts($match),
            'participants' => $match->predictionEntries,
        ]);
    }
}
