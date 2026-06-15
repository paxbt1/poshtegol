<?php

namespace App\Services\FootballData;

use App\Models\AppSetting;
use App\Models\FootballMatch;
use App\Models\MatchEvent;
use App\Models\SettlementPeriod;
use App\Models\Team;
use Carbon\Carbon;

class FootballDataMapper
{
    public function __construct(private readonly TeamCrestDownloader $crestDownloader) {}

    public function mapTeam(array $payload): Team
    {
        $externalId = $payload['id'] ?? null;
        $tla = $this->normalizeCode($payload['tla'] ?? null);
        $nameEn = $payload['name'] ?? $payload['shortName'] ?? $tla ?? 'Unknown';
        $nameFa = $this->persianTeamName($tla, $nameEn);
        $crestUrl = $payload['crest'] ?? null;
        $localCrestPath = $this->crestDownloader->download($crestUrl, $tla ?: (string) $externalId);

        $rawPayload = $payload;
        if ($localCrestPath) {
            $rawPayload['_local_crest_path'] = $localCrestPath;
        }

        $attributes = [
            'external_team_id' => $externalId,
            'tla' => $tla,
            'fifa_code' => $tla,
            'name_fa' => $nameFa,
            'name_en' => $nameEn,
            'crest_url' => $crestUrl,
            'area_name' => $payload['area']['name'] ?? null,
            'area_code' => $payload['area']['code'] ?? null,
            'last_synced_at' => now(),
            'raw_payload' => $rawPayload,
        ];

        $team = $this->findExistingTeam($externalId, $tla, $nameEn, $nameFa);

        if ($team) {
            $team->fill($attributes);
            $team->save();

            return $team;
        }

        return Team::create($attributes);
    }

    public function mapMatch(array $payload): FootballMatch
    {
        $homeTeam = $this->resolveTeam($payload['homeTeam'] ?? null);
        $awayTeam = $this->resolveTeam($payload['awayTeam'] ?? null);

        $startsAt = ! empty($payload['utcDate'])
            ? Carbon::parse($payload['utcDate'], 'UTC')->timezone('Asia/Tehran')
            : null;

        $sourceUpdatedAt = ! empty($payload['lastUpdated'])
            ? Carbon::parse($payload['lastUpdated'])
            : null;

        $stage = $this->stageKey($payload['stage'] ?? null);
        $status = $this->statusKey($payload['status'] ?? null, $payload['minute'] ?? null);
        $match = $this->findExistingMatch($payload, $homeTeam, $awayTeam, $startsAt, $stage);

        if ($match?->manual_result_locked) {
            return $match;
        }

        $homeScore = data_get($payload, 'score.fullTime.home');
        $awayScore = data_get($payload, 'score.fullTime.away');

        $attributes = [
            'football_data_id' => $payload['id'] ?? null,
            'external_fixture_id' => $payload['id'] ?? null,
            'period_id' => $match?->period_id ?? $this->periodIdForStage($stage),
            'stage' => $stage,
            'stage_label_fa' => $this->stageLabel($stage),
            'group_name' => $this->groupName($payload['group'] ?? null),
            'home_team_id' => $homeTeam?->id ?? $match?->home_team_id,
            'away_team_id' => $awayTeam?->id ?? $match?->away_team_id,
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'status' => $status,
            'api_status' => $payload['status'] ?? null,
            'api_stage' => $payload['stage'] ?? null,
            'api_group' => $payload['group'] ?? null,
            'matchday' => $payload['matchday'] ?? null,
            'minute' => $payload['minute'] ?? null,
            'injury_time' => $payload['injuryTime'] ?? null,
            'half_time_home_score' => data_get($payload, 'score.halfTime.home'),
            'half_time_away_score' => data_get($payload, 'score.halfTime.away'),
            'starts_at' => $startsAt ?? $match?->starts_at,
            'prediction_locks_at' => $startsAt ? $startsAt->copy()->subMinutes(max(0, AppSetting::getInt('prediction_lock_minutes', 60))) : $match?->prediction_locks_at,
            'timezone_source' => 'UTC',
            'source_last_updated_at' => $sourceUpdatedAt,
            'last_synced_at' => now(),
            'raw_payload' => $payload,
            'is_placeholder_match' => ! $homeTeam || ! $awayTeam,
            'bracket_slot_home' => $homeTeam ? null : ($payload['homeTeam']['name'] ?? $match?->bracket_slot_home ?? 'تیم میزبان نامشخص'),
            'bracket_slot_away' => $awayTeam ? null : ($payload['awayTeam']['name'] ?? $match?->bracket_slot_away ?? 'تیم مهمان نامشخص'),
            'entry_amount' => $match?->entry_amount ?? config('family-cup.default_entry_amount', 50000),
        ];

        if ($match) {
            $match->fill($attributes);
            $match->save();
        } else {
            $match = FootballMatch::create($attributes);
        }

        $this->syncGoalEvents($match, $payload['goals'] ?? []);

        return $match;
    }

    public function statusKey(?string $status, ?int $minute = null): string
    {
        return match ($status) {
            'SCHEDULED', 'TIMED' => 'scheduled',
            'LIVE', 'IN_PLAY' => ($minute && $minute > 45) ? 'live_second_half' : 'live_first_half',
            'PAUSED' => 'halftime',
            'FINISHED' => 'finished',
            'POSTPONED' => 'postponed',
            'SUSPENDED' => 'suspended',
            'CANCELLED' => 'cancelled',
            default => 'scheduled',
        };
    }

    private function findExistingTeam(null|int|string $externalId, ?string $tla, string $nameEn, string $nameFa): ?Team
    {
        if ($externalId) {
            $team = Team::where('external_team_id', $externalId)->first();
            if ($team) {
                return $team;
            }
        }

        if ($tla) {
            $team = Team::where('fifa_code', $tla)->orWhere('tla', $tla)->first();
            if ($team) {
                return $team;
            }
        }

        if ($nameEn !== 'Unknown') {
            return Team::where('name_en', $nameEn)->orWhere('name_fa', $nameFa)->first();
        }

        return null;
    }

    private function findExistingMatch(array $payload, ?Team $homeTeam, ?Team $awayTeam, ?Carbon $startsAt, string $stage): ?FootballMatch
    {
        $externalId = $payload['id'] ?? null;

        if ($externalId) {
            $match = FootballMatch::where('football_data_id', $externalId)
                ->orWhere('external_fixture_id', $externalId)
                ->first();

            if ($match) {
                return $match;
            }
        }

        if ($homeTeam && $awayTeam && $startsAt) {
            $match = FootballMatch::where('home_team_id', $homeTeam->id)
                ->where('away_team_id', $awayTeam->id)
                ->where('stage', $stage)
                ->whereBetween('starts_at', [$startsAt->copy()->subHours(12), $startsAt->copy()->addHours(12)])
                ->first();

            if ($match) {
                return $match;
            }

            $match = FootballMatch::where('home_team_id', $homeTeam->id)
                ->where('away_team_id', $awayTeam->id)
                ->where('stage', $stage)
                ->first();

            if ($match) {
                return $match;
            }
        }

        return null;
    }

    private function resolveTeam(?array $payload): ?Team
    {
        if (! $payload || empty($payload['id'])) {
            return null;
        }

        return $this->mapTeam($payload);
    }

    private function syncGoalEvents(FootballMatch $match, array $goals): void
    {
        foreach ($goals as $index => $goal) {
            $team = $this->resolveTeam($goal['team'] ?? null);
            $externalId = 'football-data:goal:'.$match->football_data_id.':'.($goal['minute'] ?? 'x').':'.($goal['scorer']['id'] ?? $index);

            MatchEvent::updateOrCreate(
                ['match_id' => $match->id, 'external_event_id' => $externalId],
                [
                    'minute' => $goal['minute'] ?? null,
                    'type' => 'goal',
                    'team_id' => $team?->id,
                    'title' => 'گل',
                    'description' => $goal['scorer']['name'] ?? $team?->name_fa,
                    'payload' => $goal,
                    'source' => 'football-data',
                ],
            );
        }
    }

    private function normalizeCode(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return strtoupper(trim($value));
    }

    private function persianTeamName(?string $tla, string $nameEn): string
    {
        $map = config('team-names-fa', []);

        return $map[$tla] ?? $map[$nameEn] ?? $nameEn;
    }

    private function stageKey(?string $stage): string
    {
        return match ($stage) {
            'GROUP_STAGE' => 'group',
            'LAST_32' => 'round_32',
            'LAST_16' => 'round_16',
            'QUARTER_FINALS' => 'quarter_final',
            'SEMI_FINALS' => 'semi_final',
            'THIRD_PLACE' => 'bronze_final',
            'FINAL' => 'final',
            default => 'group',
        };
    }

    private function stageLabel(string $stage): string
    {
        return match ($stage) {
            'group' => 'مرحله گروهی',
            'round_32' => 'یک‌شانزدهم نهایی',
            'round_16' => 'یک‌هشتم نهایی',
            'quarter_final' => 'یک‌چهارم نهایی',
            'semi_final' => 'نیمه‌نهایی',
            'bronze_final' => 'رده‌بندی',
            'final' => 'فینال',
            default => 'مرحله گروهی',
        };
    }

    private function groupName(?string $group): ?string
    {
        if (! $group) {
            return null;
        }

        return str_replace('GROUP_', '', $group);
    }

    private function periodIdForStage(string $stage): ?int
    {
        $type = $stage === 'group' ? 'group_stage' : 'knockout_final';

        return SettlementPeriod::where('type', $type)->value('id');
    }
}
