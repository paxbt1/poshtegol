<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\NewsArticle;
use App\Models\PredictionEntry;
use App\Models\SettlementPeriod;
use App\Models\UserPeriodResult;
use App\Services\ReferralService;
use App\Services\RankingService;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(ReferralService $referralService, RankingService $rankingService)
    {
        $user = auth()->user();
        $referralService->ensureInviteCode($user);

        $periods = SettlementPeriod::query()->orderBy('id')->get();
        $activePeriod = $periods->first();
        if ($activePeriod) {
            $rankingService->calculatePeriodRanking($activePeriod);
        }

        return view('dashboard.index', [
            'periods' => $periods,
            'todayMatches' => FootballMatch::query()
                ->with(['homeTeam', 'awayTeam'])
                ->whereDate('starts_at', today())
                ->orderBy('starts_at')
                ->take(4)
                ->get(),
            'nextMatches' => FootballMatch::query()
                ->with(['homeTeam', 'awayTeam'])
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(3)
                ->get(),
            'paidPredictions' => PredictionEntry::query()
                ->with(['match.homeTeam', 'match.awayTeam'])
                ->where('user_id', $user->id)
                ->where('payment_status', 'paid')
                ->latest()
                ->take(5)
                ->get(),
            'invitedCount' => $user->invitedUsers()->count(),
            'activePeriodResult' => $activePeriod ? UserPeriodResult::where('period_id', $activePeriod->id)->where('user_id', $user->id)->first() : null,
            'liveMatchesCount' => FootballMatch::whereIn('status', ['live_first_half', 'halftime', 'live_second_half'])->count(),
            'latestNews' => Schema::hasTable('news_articles')
                ? NewsArticle::query()
                    ->where('status', 'published')
                    ->whereNotNull('original_url')
                    ->whereNotNull('slug')
                    ->orderByDesc('is_featured')
                    ->orderByDesc('published_at')
                    ->latest()
                    ->take(6)
                    ->get()
                : collect(),
        ]);
    }
}
