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

    public function test_token_settlement_marks_debtors_and_creditors(): void
    {
        [$period, $match, $winner] = $this->seedMatch();
        $loser = User::factory()->create();

        $winnerEntry = $this->paidPrediction($winner, $match, ['full_time_result' => 'home', 'exact_home_score' => 1, 'exact_away_score' => 0, 'total_goals_option' => 'under_2_5'], 10);
        $loserEntry = $this->paidPrediction($loser, $match, ['full_time_result' => 'away', 'exact_home_score' => 0, 'exact_away_score' => 1, 'total_goals_option' => 'over_2_5'], 10);
        $this->tokenTransaction($winner, $winnerEntry, 10);
        $this->tokenTransaction($loser, $loserEntry, 10);

        $match->update(['home_score' => 1, 'away_score' => 0, 'status' => 'finished']);
        app(ScoringService::class)->calculateForMatch($match);
        app(SettlementService::class)->calculatePeriod($period, false);

        $winnerResult = $period->userResults()->where('user_id', $winner->id)->firstOrFail();
        $loserResult = $period->userResults()->where('user_id', $loser->id)->firstOrFail();

        $this->assertSame('finalized_creditor', $winnerResult->settlement_status);
        $this->assertSame(10, (int) $winnerResult->final_settlement_amount);
        $this->assertSame('finalized_debtor', $loserResult->settlement_status);
        $this->assertSame(10, (int) $loserResult->final_settlement_amount);
    }

    public function test_referral_bonus_is_calculated_in_tokens(): void
    {
        [$period, $match, $inviter] = $this->seedMatch();
        $referred = User::factory()->create(['invited_by_user_id' => $inviter->id]);
        ReferralRelation::create(['inviter_user_id' => $inviter->id, 'referred_user_id' => $referred->id]);
        $entry = $this->paidPrediction($referred, $match, ['full_time_result' => 'home', 'exact_home_score' => 1, 'exact_away_score' => 0, 'total_goals_option' => 'under_2_5'], 100);
        $this->tokenTransaction($referred, $entry, 100);
        $match->update(['home_score' => 1, 'away_score' => 0, 'status' => 'finished']);

        app(ScoringService::class)->calculateForMatch($match);
        $settlement = app(SettlementService::class)->calculatePeriod($period, false);

        $this->assertSame(100, (int) $settlement->total_reward_amount);
        $this->assertSame(0, (int) $settlement->total_gateway_fee_amount);
        $this->assertSame(3, (int) $settlement->total_referral_bonus);
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

    private function seedMatch(): array
    {
        $period = SettlementPeriod::create(['title' => 'مرحله گروهی', 'type' => 'group_stage', 'status' => 'open', 'referral_enabled' => true, 'referral_rate' => 3]);
        $home = Team::create(['name_fa' => 'ایران']);
        $away = Team::create(['name_fa' => 'ژاپن']);
        $match = FootballMatch::create(['period_id' => $period->id, 'stage' => 'group', 'home_team_id' => $home->id, 'away_team_id' => $away->id, 'status' => 'scheduled', 'starts_at' => now()->addDay(), 'prediction_locks_at' => now()->addDay()->subHour(), 'entry_amount' => 1]);
        $user = User::factory()->create();

        return [$period, $match, $user];
    }

    private function paidPrediction(User $user, FootballMatch $match, array $data, int $tokens = 10): PredictionEntry
    {
        return PredictionEntry::create(array_merge($data, [
            'user_id' => $user->id,
            'match_id' => $match->id,
            'period_id' => $match->period_id,
            'entry_amount' => $tokens,
            'gateway_fee_amount' => 0,
            'payable_amount' => $tokens,
            'payment_status' => 'paid',
            'prediction_status' => 'locked',
            'paid_at' => now(),
            'locked_at' => now(),
        ]));
    }

    private function tokenTransaction(User $user, PredictionEntry $entry, int $tokens): PaymentTransaction
    {
        return PaymentTransaction::create([
            'user_id' => $user->id,
            'prediction_entry_id' => $entry->id,
            'gateway' => 'token',
            'amount' => $tokens,
            'amount_gateway' => $tokens,
            'entry_amount' => $tokens,
            'gateway_fee_amount' => 0,
            'status' => 'paid',
        ]);
    }
}
