<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SettlementPeriod extends Model
{
    protected $fillable = [
        'title',
        'type',
        'starts_at',
        'ends_at',
        'status',
        'prize_distribution_json',
        'referral_enabled',
        'referral_rate',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'prize_distribution_json' => 'array',
            'referral_enabled' => 'boolean',
            'referral_rate' => 'decimal:2',
        ];
    }

    public function matches(): HasMany
    {
        return $this->hasMany(FootballMatch::class, 'period_id');
    }

    public function userResults(): HasMany
    {
        return $this->hasMany(UserPeriodResult::class, 'period_id');
    }

    public function settlement(): HasOne
    {
        return $this->hasOne(PeriodSettlement::class, 'period_id');
    }
}
