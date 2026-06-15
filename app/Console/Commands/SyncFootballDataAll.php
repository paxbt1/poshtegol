<?php

namespace App\Console\Commands;

use App\Services\FootballData\FootballDataSyncService;
use Illuminate\Console\Command;

class SyncFootballDataAll extends Command
{
    protected $signature = 'family-cup:football-data:sync-all';
    protected $description = 'همگام‌سازی کامل داده‌های football-data.org';

    public function handle(FootballDataSyncService $sync): int
    {
        $result = $sync->syncAll();
        $this->info($result->message ?? 'انجام شد.');

        return $result->status === 'failed' ? self::FAILURE : self::SUCCESS;
    }
}
