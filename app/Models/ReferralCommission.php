<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCommission extends Model
{
    protected $fillable = [
        'period_id',
        'inviter_user_id',
        'referred_user_id',
        'base_reward_amount',
        'commission_rate',
        'commission_amount',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_user_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
