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

    public function test_prediction_amounts_do_not_add_gateway_fee_for_offline_payment(): void
    {
        $user = User::factory()->create();
        $match = $this->match();

        $this->actingAs($user)
            ->postJson(route('matches.prediction.preview', $match), [])
            ->assertOk()
            ->assertJsonPath('entry_amount', 50000)
            ->assertJsonPath('payable_amount', 50000)
            ->assertJsonMissingPath('gateway_fee_amount');
    }

    public function test_offline_payment_receipt_is_pending_until_admin_approval(): void
    {
        $user = User::factory()->create();
        $match = $this->match();
        $entry = $this->entry($user, $match);

        $this->actingAs($user)
            ->postJson(route('predictions.pay', $entry), [
                'payer_card_number' => '6037991234567890',
                'receipt_number' => 'RCPT-1001',
            ])
            ->assertOk();

        $transaction = PaymentTransaction::firstOrFail();
        $this->assertSame('pending_review', $transaction->status);
        $this->assertSame('pending_review', $entry->fresh()->payment_status);
        $this->assertSame('6037991234567890', $transaction->request_payload['payer_card_number']);
        $this->assertSame('RCPT-1001', $transaction->request_payload['receipt_number']);
    }

    public function test_admin_approval_finalizes_offline_payment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $match = $this->match();
        $entry = $this->entry($user, $match, ['payment_status' => 'pending_review', 'prediction_status' => 'pending_review']);

        $transaction = PaymentTransaction::create([
            'user_id' => $user->id,
            'prediction_entry_id' => $entry->id,
            'gateway' => 'offline_card',
            'amount' => 50000,
            'amount_gateway' => 50000,
            'entry_amount' => 50000,
            'gateway_fee_amount' => 0,
            'reference_id' => 'RCPT-1001',
            'status' => 'pending_review',
            'request_payload' => ['payer_card_number' => '6037991234567890', 'receipt_number' => 'RCPT-1001'],
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.payment-transactions.approve', $transaction))
            ->assertOk();

        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertSame('paid', $entry->fresh()->payment_status);
        $this->assertSame('locked', $entry->fresh()->prediction_status);
    }

    private function entry(User $user, FootballMatch $match, array $overrides = []): PredictionEntry
    {
        return PredictionEntry::create(array_merge([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'period_id' => $match->period_id,
            'entry_amount' => 50000,
            'gateway_fee_amount' => 0,
            'payable_amount' => 50000,
            'full_time_result' => 'home',
            'exact_home_score' => 1,
            'exact_away_score' => 0,
            'total_goals_option' => 'under_2_5',
            'payment_status' => 'unpaid',
            'prediction_status' => 'draft',
        ], $overrides));
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
