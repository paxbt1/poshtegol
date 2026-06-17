<?php

namespace Tests\Feature;

use App\Models\FootballMatch;
use App\Models\PaymentTransaction;
use App\Models\PredictionEntry;
use App\Models\SettlementPeriod;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayFeeVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_prediction_preview_uses_selected_token_count_without_gateway_fee(): void
    {
        $user = User::factory()->create();
        $match = $this->match();

        $this->actingAs($user)
            ->postJson(route('matches.prediction.preview', $match), ['stake_tokens' => 75])
            ->assertOk()
            ->assertJsonPath('entry_amount', 75)
            ->assertJsonPath('payable_amount', 75)
            ->assertJsonMissingPath('gateway_fee_amount');
    }

    public function test_prediction_is_locked_immediately_with_token_transaction(): void
    {
        $user = User::factory()->create();
        $match = $this->match();

        $this->actingAs($user)
            ->postJson(route('matches.prediction.store', $match), $this->payload(['stake_tokens' => 120]))
            ->assertOk()
            ->assertJsonPath('entry_amount_label', '120 توکن');

        $entry = PredictionEntry::firstOrFail();
        $transaction = PaymentTransaction::firstOrFail();

        $this->assertSame('paid', $entry->payment_status);
        $this->assertSame('locked', $entry->prediction_status);
        $this->assertSame(120, (int) $entry->entry_amount);
        $this->assertSame('token', $transaction->gateway);
        $this->assertSame('paid', $transaction->status);
        $this->assertSame(120, (int) $transaction->amount);
        $this->assertSame(120, (int) $transaction->request_payload['stake_tokens']);
    }

    public function test_token_prediction_can_be_edited_before_second_half_lock(): void
    {
        $user = User::factory()->create();
        $match = $this->match();
        $entry = $this->entry($user, $match, ['payment_status' => 'paid', 'prediction_status' => 'locked', 'entry_amount' => 50, 'payable_amount' => 50]);

        $this->actingAs($user)
            ->postJson(route('matches.prediction.store', $match), $this->payload([
                'full_time_result' => 'away',
                'exact_home_score' => 0,
                'exact_away_score' => 2,
                'stake_tokens' => 150,
            ]))
            ->assertOk();

        $entry->refresh();
        $this->assertSame('away', $entry->full_time_result);
        $this->assertSame(150, (int) $entry->entry_amount);
        $this->assertSame(150, (int) $entry->transactions()->where('gateway', 'token')->firstOrFail()->amount);
    }

    public function test_prediction_is_locked_after_second_half_failsafe_even_if_status_is_stale(): void
    {
        $user = User::factory()->create();
        $match = $this->match([
            'status' => 'scheduled',
            'starts_at' => now()->subMinutes(61),
            'prediction_locks_at' => now()->subMinute(),
        ]);

        $this->actingAs($user)
            ->postJson(route('matches.prediction.store', $match), $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('match');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'full_time_result' => 'home',
            'exact_home_score' => 1,
            'exact_away_score' => 0,
            'total_goals_option' => 'under_2_5',
            'stake_tokens' => 50,
        ], $overrides);
    }

    private function entry(User $user, FootballMatch $match, array $overrides = []): PredictionEntry
    {
        return PredictionEntry::create(array_merge([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'period_id' => $match->period_id,
            'entry_amount' => 5,
            'gateway_fee_amount' => 0,
            'payable_amount' => 5,
            'full_time_result' => 'home',
            'exact_home_score' => 1,
            'exact_away_score' => 0,
            'total_goals_option' => 'under_2_5',
            'payment_status' => 'unpaid',
            'prediction_status' => 'draft',
        ], $overrides));
    }

    private function match(array $overrides = []): FootballMatch
    {
        $period = SettlementPeriod::create(['title' => 'مرحله گروهی', 'type' => 'group_stage', 'status' => 'open']);
        $home = Team::create(['name_fa' => 'ایران', 'name_en' => 'Iran']);
        $away = Team::create(['name_fa' => 'مکزیک', 'name_en' => 'Mexico']);

        return FootballMatch::create(array_merge([
            'period_id' => $period->id,
            'stage' => 'group',
            'stage_label_fa' => 'مرحله گروهی',
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'status' => 'scheduled',
            'starts_at' => now()->addDay(),
            'prediction_locks_at' => now()->addDay()->subHour(),
            'entry_amount' => 1,
        ], $overrides));
    }
}
