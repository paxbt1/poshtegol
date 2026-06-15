<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Services\MatchResultService;
use App\Services\ScoringService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MatchResultController extends Controller
{
    public function edit(FootballMatch $match)
    {
        return view('admin.match-result', ['match' => $match->load(['homeTeam', 'awayTeam', 'events.team', 'predictionEntries.user', 'predictionEntries.result'])]);
    }

    public function result(FootballMatch $match, Request $request, MatchResultService $service)
    {
        $data = $request->validate([
            'home_score' => ['required', 'integer', 'min:0', 'max:20'],
            'away_score' => ['required', 'integer', 'min:0', 'max:20'],
            'minute' => ['nullable', 'integer', 'min:0', 'max:130'],
            'status' => ['required', Rule::in(['scheduled', 'locked', 'live_first_half', 'halftime', 'live_second_half', 'finished', 'settled'])],
            'qualified_team_id' => ['nullable', Rule::in(array_filter([$match->home_team_id, $match->away_team_id]))],
        ]);

        $service->updateScore($match, $data);

        return response()->json(['message' => 'نتیجه بازی ذخیره شد.']);
    }

    public function status(FootballMatch $match, Request $request, MatchResultService $service)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['scheduled', 'locked', 'live_first_half', 'halftime', 'live_second_half', 'finished', 'settled'])]]);
        $service->updateStatus($match, $data['status']);

        return response()->json(['message' => 'وضعیت بازی به‌روزرسانی شد.']);
    }

    public function event(FootballMatch $match, Request $request, MatchResultService $service)
    {
        $data = $request->validate([
            'minute' => ['nullable', 'integer', 'min:0', 'max:130'],
            'type' => ['required', Rule::in(['goal', 'yellow_card', 'red_card', 'halftime', 'second_half_start', 'fulltime', 'manual_note'])],
            'team_id' => ['nullable', Rule::in([$match->home_team_id, $match->away_team_id])],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $service->addEvent($match, $data);

        return response()->json(['message' => 'رویداد بازی ثبت شد.']);
    }

    public function calculate(FootballMatch $match, ScoringService $service)
    {
        $count = $service->calculateForMatch($match);

        return response()->json(['message' => "امتیاز {$count} پیش‌بینی محاسبه شد."]);
    }
}
