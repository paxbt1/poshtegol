<?php

namespace Tests\Feature;

use App\Models\FootballDataSyncLog;
use App\Models\FootballMatch;
use App\Models\PaymentTransaction;
use App\Models\PredictionEntry;
use App\Models\PredictionResult;
use App\Models\SettlementPeriod;
use App\Models\Team;
use App\Models\User;
use App\Services\FootballData\FootballDataSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FootballDataSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_teams_endpoint_imports_teams(): void
    {
        config(['football-data.token' => 'test-token']);
        Http::fake([
            'api.football-data.org/v4/competitions/WC/teams*' => Http::response([
                'teams' => [
                    ['id' => 1, 'name' => 'Iran', 'tla' => 'IRN', 'area' => ['name' => 'Asia', 'code' => 'ASI']],
                ],
            ]),
        ]);

        $result = app(FootballDataSyncService::class)->syncTeams();

        $this->assertSame('success', $result->status);
        $this->assertDatabaseHas('teams', ['external_team_id' => 1, 'name_fa' => 'ایران']);
    }

    public function test_matches_endpoint_imports_null_team_fixture_without_crashing(): void
    {
        config(['football-data.token' => 'test-token']);
        Http::fake([
            'api.football-data.org/v4/competitions/WC/matches*' => Http::response([
                'matches' => [
                    [
                        'id' => 10,
                        'utcDate' => '2026-06-11T20:00:00Z',
                        'status' => 'TIMED',
                        'stage' => 'LAST_32',
                        'homeTeam' => null,
                        'awayTeam' => ['id' => 2, 'name' => 'Mexico', 'tla' => 'MEX'],
                        'score' => ['fullTime' => ['home' => null, 'away' => null]],
                    ],
                ],
            ]),
        ]);

        $result = app(FootballDataSyncService::class)->syncFixtures();

        $this->assertSame('success', $result->status);
        $this->assertDatabaseHas('matches', ['football_data_id' => 10, 'stage' => 'round_32', 'is_placeholder_match' => true]);
    }

    public function test_finished_match_sync_triggers_scoring_unless_period_finalized(): void
    {
        config(['football-data.token' => 'test-token']);
        $period = SettlementPeriod::create(['title' => 'مرحله گروهی', 'type' => 'group_stage', 'status' => 'open']);
        $home = Team::create(['name_fa' => 'ایران', 'name_en' => 'Iran', 'external_team_id' => 1, 'fifa_code' => 'IRN']);
        $away = Team::create(['name_fa' => 'مکزیک', 'name_en' => 'Mexico', 'external_team_id' => 2, 'fifa_code' => 'MEX']);
        $match = FootballMatch::create([
            'football_data_id' => 99,
            'period_id' => $period->id,
            'stage' => 'group',
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'status' => 'scheduled',
            'starts_at' => now()->subDay(),
            'prediction_locks_at' => now()->subDay()->subHour(),
        ]);
        $user = User::factory()->create();
        $entry = PredictionEntry::create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'period_id' => $period->id,
            'entry_amount' => 50000,
            'gateway_fee_amount' => 5000,
            'payable_amount' => 55000,
            'full_time_result' => 'home',
            'exact_home_score' => 2,
            'exact_away_score' => 1,
            'total_goals_option' => 'over_2_5',
            'payment_status' => 'paid',
            'prediction_status' => 'locked',
            'paid_at' => now(),
            'locked_at' => now(),
        ]);
        PaymentTransaction::create(['user_id' => $user->id, 'prediction_entry_id' => $entry->id, 'amount' => 55000, 'amount_gateway' => 550000, 'entry_amount' => 50000, 'gateway_fee_amount' => 5000, 'status' => 'paid']);

        Http::fake([
            'api.football-data.org/v4/competitions/WC/matches*' => Http::response([
                'matches' => [[
                    'id' => 99,
                    'utcDate' => '2026-06-11T20:00:00Z',
                    'status' => 'FINISHED',
                    'stage' => 'GROUP_STAGE',
                    'homeTeam' => ['id' => 1, 'name' => 'Iran', 'tla' => 'IRN'],
                    'awayTeam' => ['id' => 2, 'name' => 'Mexico', 'tla' => 'MEX'],
                    'score' => ['fullTime' => ['home' => 2, 'away' => 1]],
                ]],
            ]),
        ]);

        app(FootballDataSyncService::class)->syncFinishedAndScore();

        $this->assertSame(10, (int) PredictionResult::where('prediction_entry_id', $entry->id)->value('total_points'));

        \App\Models\PeriodSettlement::create(['period_id' => $period->id, 'status' => 'finalized']);
        PredictionResult::where('prediction_entry_id', $entry->id)->delete();
        app(FootballDataSyncService::class)->syncFinishedAndScore();

        $this->assertDatabaseMissing('prediction_results', ['prediction_entry_id' => $entry->id]);
    }

    public function test_429_response_creates_failed_sync_log(): void
    {
        config(['football-data.token' => 'test-token']);
        Http::fake([
            'api.football-data.org/v4/competitions/WC/teams*' => Http::response(['message' => 'rate limit'], 429),
        ]);

        $result = app(FootballDataSyncService::class)->syncTeams();

        $this->assertSame('failed', $result->status);
        $this->assertTrue(FootballDataSyncLog::where('status', 'failed')->where('http_status', 429)->exists());
    }
}
