<?php

namespace App\Console\Commands;

use App\Services\FootballData\FootballDataSyncService;
use Illuminate\Console\Command;

class SyncFootballDataFixtures extends Command
{
    protected $signature = 'family-cup:football-data:sync-fixtures';
    protected $description = 'همگام‌سازی برنامه بازی‌ها از football-data.org';

    public function handle(FootballDataSyncService $sync): int
    {
        $result = $sync->syncFixtures();
        $this->info($result->message ?? 'انجام شد.');

        return $result->status === 'failed' ? self::FAILURE : self::SUCCESS;
    }
}
