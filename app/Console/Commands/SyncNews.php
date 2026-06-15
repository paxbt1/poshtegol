<?php

namespace App\Console\Commands;

use App\Services\News\NewsSyncService;
use Illuminate\Console\Command;

class SyncNews extends Command
{
    protected $signature = 'family-cup:news:sync {--no-translate : Temporarily disable article translation for this run}';

    protected $description = 'Fetch and translate latest World Cup news headlines.';

    public function handle(NewsSyncService $syncService): int
    {
        $result = $syncService->sync(! $this->option('no-translate'));

        $this->info($result['message']);
        $this->line('received='.$result['received'].' created='.$result['created'].' updated='.$result['updated'].' translated='.$result['translated']);

        return $result['status'] === 'success' ? self::SUCCESS : self::FAILURE;
    }
}
