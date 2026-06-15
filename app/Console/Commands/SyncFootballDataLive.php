<?php

namespace App\Console\Commands;

use App\Services\FootballData\FootballDataSyncService;
use Illuminate\Console\Command;

class SyncFootballDataLive extends Command
{
    protected $signature = 'family-cup:football-data:sync-live';
    protected $description = 'همگام‌سازی بازی‌های امروز و زنده';

    public function handle(FootballDataSyncService $sync): int
    {
        $result = $sync->syncLiveMatches();
        $this->info($result->message ?? 'انجام شد.');

        return $result->status === 'failed' ? self::FAILURE : self::SUCCESS;
    }
}
