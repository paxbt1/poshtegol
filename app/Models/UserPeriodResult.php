<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPeriodResult extends Model
{
    protected $fillable = [
        'period_id',
        'user_id',
        'total_entries',
        'total_entry_amount',
        'total_paid_amount',
        'total_points',
        'rank',
        'reward_amount',
        'referral_bonus_amount',
        'final_settlement_amount',
        'settlement_status',
        'settled_at',
        'settlement_ref',
    ];

    protected function casts(): array
    {
        return ['settled_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(SettlementPeriod::class);
    }
}
