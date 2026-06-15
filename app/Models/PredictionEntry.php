<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PredictionEntry extends Model
{
    protected $fillable = [
        'user_id',
        'match_id',
        'period_id',
        'entry_amount',
        'gateway_fee_amount',
        'payable_amount',
        'full_time_result',
        'exact_home_score',
        'exact_away_score',
        'total_goals_option',
        'qualified_team_id',
        'payment_status',
        'prediction_status',
        'locked_at',
        'paid_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(SettlementPeriod::class, 'period_id');
    }

    public function qualifiedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'qualified_team_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function result()
    {
        return $this->hasOne(PredictionResult::class);
    }
}
