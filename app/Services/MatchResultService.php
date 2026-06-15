<?php

namespace App\Services;

use App\Events\MatchScoreUpdated;
use App\Events\MatchStatusChanged;
use App\Models\FootballMatch;
use App\Models\MatchEvent;

class MatchResultService
{
    public function updateScore(FootballMatch $match, array $data): FootballMatch
    {
        $metadata = $match->metadata ?? [];
        $metadata['minute'] = $data['minute'] ?? ($metadata['minute'] ?? null);

        $match->update([
            'home_score' => $data['home_score'],
            'away_score' => $data['away_score'],
            'qualified_team_id' => $data['qualified_team_id'] ?? null,
            'status' => $data['status'] ?? $match->status,
            'metadata' => $metadata,
        ]);

        event(new MatchScoreUpdated($match->fresh()));

        return $match->fresh();
    }

    public function updateStatus(FootballMatch $match, string $status): FootballMatch
    {
        $match->update(['status' => $status]);
        event(new MatchStatusChanged($match->fresh()));

        return $match->fresh();
    }

    public function addEvent(FootballMatch $match, array $data): MatchEvent
    {
        return $match->events()->create($data);
    }
}
