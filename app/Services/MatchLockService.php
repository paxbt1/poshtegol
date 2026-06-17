<?php

namespace App\Services;

use App\Models\FootballMatch;
use Carbon\CarbonInterface;

class MatchLockService
{
    private const LOCKED_STATUSES = [
        'live',
        'live_second_half',
        'finished',
        'settled',
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
        $secondHalfFailsafe = $match->starts_at->copy()->addMinutes(60);

        if ($match->prediction_locks_at && $match->prediction_locks_at->gt($match->starts_at)) {
            return $match->prediction_locks_at->lt($secondHalfFailsafe)
                ? $match->prediction_locks_at
                : $secondHalfFailsafe;
        }

        return $secondHalfFailsafe;
    }

    public function reason(FootballMatch $match): ?string
    {
        if ($this->canPredict($match)) {
            return null;
        }

        if (in_array($match->status, ['finished', 'settled', 'awarded', 'after_extra_time', 'after_penalties'], true)) {
            return 'این بازی پایان یافته و امکان ثبت یا ویرایش پیش‌بینی ندارد.';
        }

        if ($match->status === 'live_second_half') {
            return 'نیمه دوم شروع شده و پیش‌بینی این بازی بسته شده است.';
        }

        return 'مهلت ثبت و ویرایش پیش‌بینی این بازی به پایان رسیده است.';
    }
}
