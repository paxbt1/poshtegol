<?php

namespace Tests\Feature;

use App\Models\AppSetting;
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

    public function test_prediction_amounts_store_gateway_fee_but_user_pages_hide_fee_label(): void
    {
        $user = User::factory()->create();
        $match = $this->match();

        $this->actingAs($user)
            ->postJson(route('matches.prediction.preview', $match), [])
            ->assertOk()
            ->assertJsonMissingPath('gateway_fee_amount')
            ->assertJsonMissingPath('gateway_fee_label');

        $this->actingAs($user)
            ->get(route('matches.show', $match))
            ->assertOk()
            ->assertDontSee('کارمزد درگاه');
    }

    public function test_admin_payment_page_shows_gateway_fee(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $match = $this->match();
        $entry = PredictionEntry::create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'period_id' => $match->period_id,
            'entry_amount' => 50000,
            'gateway_fee_amount' => 5000,
            'payable_amount' => 55000,
            'payment_status' => 'paid',
            'prediction_status' => 'locked',
        ]);
        PaymentTransaction::create([
            'user_id' => $user->id,
            'prediction_entry_id' => $entry->id,
            'amount' => 55000,
            'amount_gateway' => 550000,
            'entry_amount' => 50000,
            'gateway_fee_amount' => 5000,
            'status' => 'paid',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.payments'))
            ->assertOk()
            ->assertSee('کارمزد درگاه');
    }

    public function test_payment_requires_zibal_merchant_id_outside_sandbox(): void
    {
        AppSetting::setValue('payment_driver', 'zibal');
        AppSetting::setValue('zibal_sandbox', false);
        AppSetting::setValue('zibal_merchant_id', '');

        $user = User::factory()->create();
        $match = $this->match();
        $entry = PredictionEntry::create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'period_id' => $match->period_id,
            'entry_amount' => 50000,
            'gateway_fee_amount' => 5000,
            'payable_amount' => 55000,
            'payment_status' => 'unpaid',
            'prediction_status' => 'draft',
        ]);

        $this->actingAs($user)
            ->postJson(route('predictions.pay', $entry))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment');

        $this->assertDatabaseCount('payment_transactions', 0);
        $this->assertSame('unpaid', $entry->fresh()->payment_status);
    }

    public function test_pending_payment_with_gateway_token_redirects_to_existing_zibal_transaction(): void
    {
        $user = User::factory()->create();
        $match = $this->match();
        $entry = PredictionEntry::create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'period_id' => $match->period_id,
            'entry_amount' => 50000,
            'gateway_fee_amount' => 5000,
            'payable_amount' => 55000,
            'payment_status' => 'pending',
            'prediction_status' => 'pending_payment',
        ]);
        PaymentTransaction::create([
            'user_id' => $user->id,
            'prediction_entry_id' => $entry->id,
            'gateway' => 'zibal',
            'amount' => 55000,
            'amount_gateway' => 550000,
            'entry_amount' => 50000,
            'gateway_fee_amount' => 5000,
            'transaction_id' => '123456',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->postJson(route('predictions.pay', $entry))
            ->assertOk()
            ->assertJsonPath('redirect', 'https://gateway.zibal.ir/start/123456');
    }

    private function match(): FootballMatch
    {
        $period = SettlementPeriod::create(['title' => 'مرحله گروهی', 'type' => 'group_stage', 'status' => 'open']);
        $home = Team::create(['name_fa' => 'ایران', 'name_en' => 'Iran']);
        $away = Team::create(['name_fa' => 'مکزیک', 'name_en' => 'Mexico']);

        return FootballMatch::create([
            'period_id' => $period->id,
            'stage' => 'group',
            'stage_label_fa' => 'مرحله گروهی',
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'status' => 'scheduled',
            'starts_at' => now()->addDay(),
            'prediction_locks_at' => now()->addDay()->subHour(),
            'entry_amount' => 50000,
        ]);
    }
}
