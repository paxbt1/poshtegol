<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\FootballMatch;

class MatchLockService
{
    public function canPredict(FootballMatch $match): bool
    {
        return now()->lt($this->lockTime($match));
    }

    public function lockTime(FootballMatch $match)
    {
        $minutes = max(0, AppSetting::getInt('prediction_lock_minutes', 60));

        return $match->prediction_locks_at ?: $match->starts_at->copy()->subMinutes($minutes);
    }

    public function reason(FootballMatch $match): ?string
    {
        if ($this->canPredict($match)) {
            return null;
        }

        return 'زمان پیش‌بینی این بازی به پایان رسیده است.';
    }
}
