<?php

namespace App\Console\Commands;

use App\Services\FootballData\FootballDataSyncService;
use Illuminate\Console\Command;

class SyncFootballDataTeams extends Command
{
    protected $signature = 'family-cup:football-data:sync-teams';
    protected $description = 'همگام‌سازی تیم‌ها از football-data.org';

    public function handle(FootballDataSyncService $sync): int
    {
        $result = $sync->syncTeams();
        $this->info($result->message ?? 'انجام شد.');

        return $result->status === 'failed' ? self::FAILURE : self::SUCCESS;
    }
}
