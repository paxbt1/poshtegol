<?php

namespace App\Console\Commands;

use App\Services\FootballData\FootballDataSyncService;
use Illuminate\Console\Command;

class SyncFootballDataResults extends Command
{
    protected $signature = 'family-cup:football-data:sync-results';
    protected $description = 'همگام‌سازی نتایج و اجرای امتیازدهی خودکار';

    public function handle(FootballDataSyncService $sync): int
    {
        $result = $sync->syncFinishedAndScore();
        $this->info($result->message ?? 'انجام شد.');

        return $result->status === 'failed' ? self::FAILURE : self::SUCCESS;
    }
}
