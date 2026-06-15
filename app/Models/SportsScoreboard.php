<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SportsScoreboard extends Model
{
    protected $fillable = [
        'provider', 'external_id', 'competition', 'season', 'home_team_name', 'away_team_name',
        'home_team_logo', 'away_team_logo', 'local_home_team_logo', 'local_away_team_logo',
        'starts_at', 'status', 'minute', 'home_score', 'away_score', 'raw_payload', 'last_synced_at', 'hash',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }
}
