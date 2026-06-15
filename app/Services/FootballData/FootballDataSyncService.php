<?php

namespace App\Services\FootballData;

use App\Models\FootballDataSyncLog;
use App\Models\FootballMatch;
use App\Models\PeriodSettlement;
use App\Services\ScoringService;
use Carbon\Carbon;
use Throwable;

class FootballDataSyncService
{
    public function __construct(
        private readonly FootballDataClient $client,
        private readonly FootballDataMapper $mapper,
        private readonly ScoringService $scoringService,
    ) {}

    public function syncTeams(): SyncResult
    {
        return $this->run('teams', function () {
            $payload = $this->client->teams();
            $teams = $payload['teams'] ?? [];
            $created = 0;
            $updated = 0;

            foreach ($teams as $teamPayload) {
                $team = $this->mapper->mapTeam($teamPayload);
                $team->wasRecentlyCreated ? $created++ : $updated++;
            }

            return new SyncResult('teams', 'success', count($teams), $created, $updated, 'تیم‌ها همگام‌سازی شدند.');
        });
    }

    public function syncFixtures(): SyncResult
    {
        return $this->syncMatches('fixtures');
    }

    public function syncTodayMatches(): SyncResult
    {
        $today = now('Asia/Tehran')->toDateString();

        return $this->syncMatches('today', [
            'dateFrom' => $today,
            'dateTo' => $today,
        ]);
    }

    public function syncLiveMatches(): SyncResult
    {
        return $this->syncTodayMatches();
    }

    public function syncFinishedAndScore(): SyncResult
    {
        return $this->syncMatches('results', [], true);
    }

    public function syncMatchByExternalId(int $id): SyncResult
    {
        return $this->run('match', function () use ($id) {
            $payload = $this->client->match($id);
            $matchPayload = $payload['match'] ?? $payload;
            $match = $this->mapper->mapMatch($matchPayload);
            $this->scoreIfReady($match);

            return new SyncResult('match', 'success', 1, $match->wasRecentlyCreated ? 1 : 0, $match->wasRecentlyCreated ? 0 : 1, 'بازی همگام‌سازی شد.');
        });
    }

    public function syncAll(): SyncResult
    {
        $teams = $this->syncTeams();
        $fixtures = $this->syncFixtures();
        $results = $this->syncFinishedAndScore();
        $allSuccessful = collect([$teams, $fixtures, $results])->every(fn (SyncResult $result) => $result->status === 'success');

        return new SyncResult(
            'all',
            $allSuccessful ? 'success' : 'partial',
            $teams->itemsReceived + $fixtures->itemsReceived + $results->itemsReceived,
            $teams->itemsCreated + $fixtures->itemsCreated + $results->itemsCreated,
            $teams->itemsUpdated + $fixtures->itemsUpdated + $results->itemsUpdated,
            $allSuccessful
                ? 'همگام‌سازی کامل تیم‌ها، برنامه بازی‌ها و نتایج انجام شد.'
                : 'بخشی از همگام‌سازی خارجی کامل نشد؛ لاگ جزئیات را بررسی کنید.',
        );
    }

    private function syncMatches(string $type, array $query = [], bool $scoreFinished = false): SyncResult
    {
        return $this->run($type, function () use ($type, $query, $scoreFinished) {
            $payload = $this->client->fixtures($query);
            $matches = $payload['matches'] ?? [];
            $created = 0;
            $updated = 0;

            foreach ($matches as $matchPayload) {
                $existingByExternalId = ! empty($matchPayload['id'])
                    ? FootballMatch::where('football_data_id', $matchPayload['id'])
                        ->orWhere('external_fixture_id', $matchPayload['id'])
                        ->first()
                    : null;

                if ($existingByExternalId?->manual_result_locked) {
                    continue;
                }

                $match = $this->mapper->mapMatch($matchPayload);
                $match->wasRecentlyCreated ? $created++ : $updated++;

                if ($scoreFinished || $match->status === 'finished') {
                    $this->scoreIfReady($match);
                }
            }

            return new SyncResult($type, 'success', count($matches), $created, $updated, 'بازی‌ها همگام‌سازی شدند.');
        });
    }

    private function scoreIfReady(FootballMatch $match): void
    {
        if ($match->status !== 'finished' || $match->home_score === null || $match->away_score === null || ! $match->period_id) {
            return;
        }

        if (PeriodSettlement::where('period_id', $match->period_id)->whereIn('status', ['finalized', 'paid'])->exists()) {
            return;
        }

        try {
            $this->scoringService->calculateForMatch($match);
            $match->update(['auto_score_calculated_at' => now()]);
        } catch (Throwable) {
            // Admin can inspect and rerun scoring manually.
        }
    }

    private function run(string $type, callable $callback): SyncResult
    {
        $startedAt = Carbon::now();

        try {
            /** @var SyncResult $result */
            $result = $callback();
            FootballDataSyncLog::create([
                'type' => $type,
                'status' => $result->status,
                'items_received' => $result->itemsReceived,
                'items_created' => $result->itemsCreated,
                'items_updated' => $result->itemsUpdated,
                'message' => $result->message,
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);

            return $result;
        } catch (FootballDataException $e) {
            if ($e->status !== null || $e->url !== null || $e->payload !== []) {
                $logData = [
                    'type' => $type,
                    'status' => 'failed',
                    'requested_url' => $e->url,
                    'http_status' => $e->status,
                    'message' => $e->getMessage(),
                    'started_at' => $startedAt,
                    'finished_at' => now(),
                ];

                if ($e->payload !== []) {
                    $logData['error_payload'] = $e->payload;
                }

                FootballDataSyncLog::create($logData);
            }

            return new SyncResult($type, 'failed', 0, 0, 0, $e->getMessage());
        } catch (Throwable $e) {
            FootballDataSyncLog::create([
                'type' => $type,
                'status' => 'failed',
                'message' => $e->getMessage(),
                'error_payload' => [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);

            $message = app()->environment('local')
                ? 'خطای همگام‌سازی: '.$e->getMessage()
                : 'همگام‌سازی با خطا روبه‌رو شد.';

            return new SyncResult($type, 'failed', 0, 0, 0, $message);
        }
    }
}
