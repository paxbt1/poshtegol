<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchEvent extends Model
{
    protected $fillable = ['match_id', 'minute', 'type', 'team_id', 'title', 'description', 'payload', 'source', 'external_event_id'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
