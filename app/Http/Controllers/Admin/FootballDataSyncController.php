<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\FootballDataSyncLog;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\FootballData\FootballDataSyncService;
use Illuminate\Http\Request;

class FootballDataSyncController extends Controller
{
    public function index()
    {
        return view('admin.football-sync', [
            'enabled' => AppSetting::getBool('football_data_enabled', (bool) config('football-data.enabled')),
            'hasToken' => (bool) AppSetting::getValue('football_data_api_token', config('football-data.token')), 
            'lastLogs' => FootballDataSyncLog::latest()->take(10)->get(),
            'teamsCount' => Team::count(),
            'matchesCount' => FootballMatch::count(),
            'scheduledCount' => FootballMatch::where('status', 'scheduled')->count(),
            'liveCount' => FootballMatch::whereIn('status', ['live_first_half', 'halftime', 'live_second_half'])->count(),
            'finishedCount' => FootballMatch::where('status', 'finished')->count(),
            'failedCount' => FootballDataSyncLog::where('status', 'failed')->count(),
        ]);
    }

    public function run(Request $request, FootballDataSyncService $sync)
    {
        $action = $request->validate([
            'action' => ['required', 'in:teams,fixtures,today,results,all'],
        ])['action'];

        $result = match ($action) {
            'teams' => $sync->syncTeams(),
            'fixtures' => $sync->syncFixtures(),
            'today' => $sync->syncTodayMatches(),
            'results' => $sync->syncFinishedAndScore(),
            'all' => $sync->syncAll(),
        };

        return response()->json([
            'message' => $result->message ?? 'همگام‌سازی انجام شد.',
            'result' => $result->toArray(),
            'redirect' => route('admin.football-data.index'),
        ], $result->status === 'failed' ? 422 : 200);
    }
}
