<?php

namespace App\Services;

use App\Models\PredictionEntry;

class LivePredictionStatusService
{
    public function evaluate(PredictionEntry $entry): array
    {
        $match = $entry->match;

        if (! $match || $match->home_score === null || $match->away_score === null) {
            return $this->status('waiting', 'منتظر پایان بازی', 'badge-locked');
        }

        $home = (int) $match->home_score;
        $away = (int) $match->away_score;
        $actual = $home > $away ? 'home' : ($home < $away ? 'away' : 'draw');
        $exact = (int) $entry->exact_home_score === $home && (int) $entry->exact_away_score === $away;
        $resultOk = $entry->full_time_result === $actual;

        if ($match->status === 'finished' || $match->status === 'settled') {
            return ($exact || $resultOk)
                ? $this->status('final_winner', 'برنده نهایی', 'badge-open')
                : $this->status('final_loser', 'بازنده نهایی', 'badge-finished');
        }

        if ($exact || $resultOk) {
            return $this->status('currently_correct', 'فعلاً درست', 'badge-open');
        }

        $canStillExact = (int) $entry->exact_home_score >= $home && (int) $entry->exact_away_score >= $away;
        if ($canStillExact) {
            return $this->status('still_chance', 'هنوز شانس دارید', 'badge-closing');
        }

        return $this->status('eliminated', 'حذف شده', 'badge-finished');
    }

    private function status(string $key, string $label, string $class): array
    {
        return ['key' => $key, 'label' => $label, 'class' => $class];
    }
}
