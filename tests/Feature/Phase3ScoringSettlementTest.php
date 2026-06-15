<?php

namespace Tests\Feature;

use App\Models\FootballMatch;
use App\Models\PaymentTransaction;
use App\Models\PredictionEntry;
use App\Models\ReferralRelation;
use App\Models\SettlementPeriod;
use App\Models\Team;
use App\Models\User;
use App\Services\ScoringService;
use App\Services\SettlementService;
use Database\Seeders\Fifa2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3ScoringSettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_scoring_exact_result_and_total_goals(): void
    {
        [$period, $match, $user] = $this->seedMatch();
        $entry = $this->paidPrediction($user, $match, ['full_time_result' => 'home', 'exact_home_score' => 2, 'exact_away_score' => 1, 'total_goals_option' => 'over_2_5']);
        $match->update(['home_score' => 2, 'away_score' => 1, 'status' => 'finished']);

        $result = app(ScoringService::class)->calculateForPrediction($entry->refresh());

        $this->assertSame(10, $result->total_points);
    }

    public function test_gateway_fee_is_not_in_reward_pool_and_referral_is_three_percent(): void
    {
        [$period, $match, $inviter] = $this->seedMatch();
        $referred = User::factory()->create(['invited_by_user_id' => $inviter->id]);
        ReferralRelation::create(['inviter_user_id' => $inviter->id, 'referred_user_id' => $referred->id]);
        $entry = $this->paidPrediction($referred, $match, ['full_time_result' => 'home', 'exact_home_score' => 1, 'exact_away_score' => 0, 'total_goals_option' => 'under_2_5']);
        PaymentTransaction::create(['user_id' => $referred->id, 'prediction_entry_id' => $entry->id, 'amount' => 55000, 'amount_gateway' => 550000, 'entry_amount' => 50000, 'gateway_fee_amount' => 5000, 'status' => 'paid']);
        $match->update(['home_score' => 1, 'away_score' => 0, 'status' => 'finished']);

        app(ScoringService::class)->calculateForMatch($match);
        $settlement = app(SettlementService::class)->calculatePeriod($period, false);

        $this->assertSame(50000, (int) $settlement->total_reward_amount);
        $this->assertSame(5000, (int) $settlement->total_gateway_fee_amount);
        $this->assertSame(1500, (int) $settlement->total_referral_bonus);
    }

    public function test_fifa_2026_seed_creates_real_fixture_counts(): void
    {
        $this->seed(Fifa2026Seeder::class);

        $this->assertSame(48, Team::count());
        $this->assertSame(104, FootballMatch::count());

        $match = FootballMatch::with(['homeTeam', 'awayTeam'])->where('match_number', 1)->firstOrFail();
        $this->assertSame('Mexico', $match->homeTeam->name_en);
        $this->assertSame('South Africa', $match->awayTeam->name_en);

        $iran = Team::where('fifa_code', 'IRN')->firstOrFail();
        $this->assertSame('G', $iran->group_name);
    }

    public function test_payment_result_is_visible_only_to_owner_or_admin(): void
    {
        [$period, $match, $owner] = $this->seedMatch();
        $other = User::factory()->create();
        $entry = $this->paidPrediction($owner, $match, ['full_time_result' => 'home', 'exact_home_score' => 1, 'exact_away_score' => 0, 'total_goals_option' => 'under_2_5']);
        $transaction = PaymentTransaction::create([
            'user_id' => $owner->id,
            'prediction_entry_id' => $entry->id,
            'amount' => 55000,
            'amount_gateway' => 550000,
            'entry_amount' => 50000,
            'gateway_fee_amount' => 5000,
            'status' => 'paid',
        ]);

        $this->actingAs($other)->getJson(route('payment.result', $transaction))->assertForbidden();
    }

    private function seedMatch(): array
    {
        $period = SettlementPeriod::create(['title' => 'مرحله گروهی', 'type' => 'group_stage', 'status' => 'open', 'referral_enabled' => true, 'referral_rate' => 3]);
        $home = Team::create(['name_fa' => 'ایران']);
        $away = Team::create(['name_fa' => 'ژاپن']);
        $match = FootballMatch::create(['period_id' => $period->id, 'stage' => 'group', 'home_team_id' => $home->id, 'away_team_id' => $away->id, 'status' => 'scheduled', 'starts_at' => now()->addDay(), 'prediction_locks_at' => now()->addDay()->subHour(), 'entry_amount' => 50000]);
        $user = User::factory()->create();

        return [$period, $match, $user];
    }

    private function paidPrediction(User $user, FootballMatch $match, array $data): PredictionEntry
    {
        return PredictionEntry::create(array_merge($data, ['user_id' => $user->id, 'match_id' => $match->id, 'period_id' => $match->period_id, 'entry_amount' => 50000, 'gateway_fee_amount' => 5000, 'payable_amount' => 55000, 'payment_status' => 'paid', 'prediction_status' => 'locked', 'paid_at' => now(), 'locked_at' => now()]));
    }
}
