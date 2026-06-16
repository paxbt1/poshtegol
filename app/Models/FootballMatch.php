<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FootballMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'match_number',
        'external_fixture_id',
        'football_data_id',
        'period_id',
        'stage',
        'group_name',
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'qualified_team_id',
        'status',
        'api_status',
        'api_stage',
        'api_group',
        'matchday',
        'minute',
        'injury_time',
        'half_time_home_score',
        'half_time_away_score',
        'kickoff_at_et',
        'starts_at',
        'prediction_locks_at',
        'timezone_source',
        'venue',
        'city',
        'country',
        'match_day_label_fa',
        'stage_label_fa',
        'bracket_slot_home',
        'bracket_slot_away',
        'is_placeholder_match',
        'metadata',
        'source_last_updated_at',
        'last_synced_at',
        'auto_score_calculated_at',
        'manual_result_locked',
        'raw_payload',
        'entry_amount',
    ];

    protected function casts(): array
    {
        return [
            'kickoff_at_et' => 'datetime',
            'starts_at' => 'datetime',
            'prediction_locks_at' => 'datetime',
            'metadata' => 'array',
            'raw_payload' => 'array',
            'is_placeholder_match' => 'boolean',
            'manual_result_locked' => 'boolean',
            'source_last_updated_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'auto_score_calculated_at' => 'datetime',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(SettlementPeriod::class, 'period_id');
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function qualifiedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'qualified_team_id');
    }

    public function predictionEntries(): HasMany
    {
        return $this->hasMany(PredictionEntry::class, 'match_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id')->orderBy('minute')->orderBy('id');
    }

    public function predictionState(): string
    {
        if (in_array($this->status, ['finished', 'awarded', 'after_extra_time', 'after_penalties'], true)) {
            return 'finished';
        }

        if (! app(\App\Services\MatchLockService::class)->canPredict($this)) {
            return 'locked';
        }

        if (now()->diffInMinutes(app(\App\Services\MatchLockService::class)->lockTime($this), false) <= 180) {
            return 'closing';
        }

        return 'open';
    }
}
