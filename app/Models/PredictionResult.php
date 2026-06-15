<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionResult extends Model
{
    protected $fillable = [
        'prediction_entry_id',
        'full_time_points',
        'exact_score_points',
        'total_goals_points',
        'qualified_team_points',
        'total_points',
        'calculated_at',
        'status',
    ];

    protected function casts(): array
    {
        return ['calculated_at' => 'datetime'];
    }

    public function predictionEntry(): BelongsTo
    {
        return $this->belongsTo(PredictionEntry::class);
    }
}
