<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\FootballMatch;
use Carbon\CarbonInterface;

class MatchLockService
{
    private const LOCKED_STATUSES = [
        'live',
        'live_first_half',
        'halftime',
        'live_second_half',
        'finished',
        'awarded',
        'after_extra_time',
        'after_penalties',
        'cancelled',
        'postponed',
        'suspended',
    ];

    public function canPredict(FootballMatch $match): bool
    {
        if (! $match->starts_at) {
            return false;
        }

        if (in_array($match->status, self::LOCKED_STATUSES, true)) {
            return false;
        }

        return now()->lt($this->lockTime($match));
    }

    public function lockTime(FootballMatch $match): CarbonInterface
    {
        $minutes = max(0, AppSetting::getInt('prediction_lock_minutes', 60));
        $configuredLock = $match->prediction_locks_at ?: $match->starts_at->copy()->subMinutes($minutes);
        $firstHalfFailsafe = $match->starts_at->copy()->addMinutes(55);

        return $configuredLock->lt($firstHalfFailsafe) ? $configuredLock : $firstHalfFailsafe;
    }

    public function reason(FootballMatch $match): ?string
    {
        if ($this->canPredict($match)) {
            return null;
        }

        if (in_array($match->status, ['finished', 'awarded', 'after_extra_time', 'after_penalties'], true)) {
            return 'این بازی پایان یافته و امکان ثبت پیش‌بینی ندارد.';
        }

        return 'زمان پیش‌بینی این بازی به پایان رسیده است.';
    }
}
