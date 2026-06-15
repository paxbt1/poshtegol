<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodSettlement extends Model
{
    protected $fillable = [
        'period_id',
        'total_entry_amount',
        'total_paid_amount',
        'total_gateway_fee_amount',
        'total_reward_amount',
        'total_referral_bonus',
        'net_admin_amount',
        'status',
        'calculated_at',
        'finalized_at',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'calculated_at' => 'datetime',
            'finalized_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(SettlementPeriod::class);
    }
}
