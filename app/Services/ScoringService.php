<?php

namespace App\Services;

use App\Models\FootballMatch;
use App\Models\PredictionEntry;
use App\Models\PredictionResult;
use App\Models\PeriodSettlement;
use Illuminate\Validation\ValidationException;

class ScoringService
{
    public function calculateForMatch(FootballMatch $match): int
    {
        if ($match->status !== 'finished' && $match->status !== 'settled') {
            throw ValidationException::withMessages(['match' => 'امتیازدهی فقط پس از پایان بازی انجام می‌شود.']);
        }

        if ($this->isPeriodFinalized($match)) {
            throw ValidationException::withMessages(['match' => 'تسویه این دوره نهایی شده و محاسبه دوباره مجاز نیست.']);
        }

        $count = 0;
        $match->predictionEntries()
            ->where('payment_status', 'paid')
            ->where('prediction_status', 'locked')
            ->each(function (PredictionEntry $entry) use ($match, &$count) {
                $this->calculateForPrediction($entry->loadMissing('match'), $match);
                $count++;
            });

        return $count;
    }

    public function calculateForPrediction(PredictionEntry $entry, ?FootballMatch $match = null): PredictionResult
    {
        $match ??= $entry->match;
        $homeScore = (int) $match->home_score;
        $awayScore = (int) $match->away_score;
        $totalGoals = $homeScore + $awayScore;
        $actualResult = $homeScore > $awayScore ? 'home' : ($homeScore < $awayScore ? 'away' : 'draw');

        $fullTimePoints = $entry->full_time_result === $actualResult ? 3 : 0;
        $exactScorePoints = ((int) $entry->exact_home_score === $homeScore && (int) $entry->exact_away_score === $awayScore) ? 5 : 0;
        $totalGoalsOption = $totalGoals < 3 ? 'under_2_5' : 'over_2_5';
        $totalGoalsPoints = $entry->total_goals_option === $totalGoalsOption ? 2 : 0;
        $qualifiedPoints = 0;

        if ($match->stage !== 'group' && $entry->qualified_team_id && $match->qualified_team_id) {
            $qualifiedPoints = (int) $entry->qualified_team_id === (int) $match->qualified_team_id ? 3 : 0;
        }

        return PredictionResult::updateOrCreate(
            ['prediction_entry_id' => $entry->id],
            [
                'full_time_points' => $fullTimePoints,
                'exact_score_points' => $exactScorePoints,
                'total_goals_points' => $totalGoalsPoints,
                'qualified_team_points' => $qualifiedPoints,
                'total_points' => $fullTimePoints + $exactScorePoints + $totalGoalsPoints + $qualifiedPoints,
                'calculated_at' => now(),
                'status' => 'final',
            ],
        );
    }

    private function isPeriodFinalized(FootballMatch $match): bool
    {
        return PeriodSettlement::where('period_id', $match->period_id)->whereIn('status', ['finalized', 'paid'])->exists();
    }
}
