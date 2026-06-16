<?php

namespace App\Services;

use App\Models\FinancialLedger;
use App\Models\PaymentTransaction;
use App\Models\PeriodSettlement;
use App\Models\PredictionEntry;
use App\Models\ReferralCommission;
use App\Models\ReferralRelation;
use App\Models\SettlementPeriod;
use App\Models\UserPeriodResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SettlementService
{
    public function __construct(private readonly RankingService $rankingService) {}

    public function calculatePeriod(SettlementPeriod $period, bool $dryRun = true): PeriodSettlement
    {
        $existing = PeriodSettlement::where('period_id', $period->id)->first();
        if ($existing && in_array($existing->status, ['finalized', 'paid'], true) && ! $dryRun) {
            throw ValidationException::withMessages(['period' => 'این دوره قبلا نهایی شده است.']);
        }

        return DB::transaction(function () use ($period, $dryRun) {
            $rankings = $this->rankingService->calculatePeriodRanking($period);
            $entries = PredictionEntry::where('period_id', $period->id)->where('payment_status', 'paid')->get();
            $totalEntry = (int) $entries->sum('entry_amount');
            $totalPaid = (int) $entries->sum('payable_amount');
            $totalGatewayFee = (int) PaymentTransaction::whereHas('predictionEntry', fn ($q) => $q->where('period_id', $period->id))->where('status', 'paid')->sum('gateway_fee_amount');

            $distribution = $this->distribution($period, $rankings->where('total_points', '>', 0)->count());
            $totalReward = 0;

            foreach ($rankings as $row) {
                $percent = $distribution[$row->rank] ?? 0;
                $reward = (int) round($totalEntry * $percent / 100);
                $totalReward += $reward;
                $row->update([
                    'reward_amount' => $reward,
                    'referral_bonus_amount' => 0,
                    'final_settlement_amount' => 0,
                    'settlement_status' => $dryRun ? 'calculated' : 'finalized',
                ]);
            }

            $totalReferral = $this->calculateReferralBonuses($period, ! $dryRun);
            $this->applyTokenSettlementStatuses($period, $dryRun);
            $totalFinalReward = (int) UserPeriodResult::where('period_id', $period->id)->sum('reward_amount');

            $settlement = PeriodSettlement::updateOrCreate(
                ['period_id' => $period->id],
                [
                    'total_entry_amount' => $totalEntry,
                    'total_paid_amount' => $totalPaid,
                    'total_gateway_fee_amount' => $totalGatewayFee,
                    'total_reward_amount' => $totalFinalReward,
                    'total_referral_bonus' => $totalReferral,
                    'net_admin_amount' => $totalGatewayFee - $totalReferral,
                    'status' => $dryRun ? 'calculated' : 'finalized',
                    'calculated_at' => now(),
                    'finalized_at' => $dryRun ? null : now(),
                ],
            );

            if (! $dryRun) {
                $period->update(['status' => 'calculated']);
                $this->writeLedgers($period);
            }

            return $settlement->refresh();
        });
    }

    public function finalizePeriod(SettlementPeriod $period): PeriodSettlement
    {
        return $this->calculatePeriod($period, false);
    }

    public function markPaid(SettlementPeriod $period): PeriodSettlement
    {
        $settlement = PeriodSettlement::where('period_id', $period->id)->firstOrFail();
        $settlement->update(['status' => 'paid', 'paid_at' => now()]);
        UserPeriodResult::where('period_id', $period->id)->update(['settlement_status' => 'paid', 'settled_at' => now()]);
        return $settlement;
    }

    private function distribution(SettlementPeriod $period, int $eligibleCount): array
    {
        if ($period->prize_distribution_json) {
            return collect($period->prize_distribution_json)->mapWithKeys(fn ($value, $key) => [(int) $key => (float) $value])->all();
        }

        return match ($eligibleCount) {
            0 => [],
            1 => [1 => 100],
            2 => [1 => 70, 2 => 30],
            default => [1 => 50, 2 => 30, 3 => 20],
        };
    }

    private function calculateReferralBonuses(SettlementPeriod $period, bool $finalized): int
    {
        UserPeriodResult::where('period_id', $period->id)->update(['referral_bonus_amount' => 0]);
        ReferralCommission::where('period_id', $period->id)->update(['status' => 'cancelled', 'commission_amount' => 0]);

        if ($period->type !== 'group_stage') {
            return 0;
        }

        $total = 0;
        $rate = (float) ($period->referral_rate ?: 3);

        $relations = ReferralRelation::query()
            ->when($period->starts_at, fn ($query) => $query->where(fn ($nested) => $nested->whereNull('active_until')->orWhere('active_until', '>=', $period->starts_at)))
            ->with('referred')
            ->get();
        foreach ($relations as $relation) {
            $referredResult = UserPeriodResult::where('period_id', $period->id)->where('user_id', $relation->referred_user_id)->first();
            if (! $referredResult || $referredResult->reward_amount <= 0) {
                continue;
            }

            $amount = (int) floor($referredResult->reward_amount * $rate / 100);
            $total += $amount;

            ReferralCommission::updateOrCreate(
                ['period_id' => $period->id, 'inviter_user_id' => $relation->inviter_user_id, 'referred_user_id' => $relation->referred_user_id],
                [
                    'base_reward_amount' => $referredResult->reward_amount,
                    'commission_rate' => $rate,
                    'commission_amount' => $amount,
                    'status' => $finalized ? 'finalized' : 'calculated',
                    'metadata' => [
                        'rule' => 'group_stage_positive_reward',
                        'calculated_from_reward_amount' => $referredResult->reward_amount,
                    ],
                ],
            );

            $inviterResult = UserPeriodResult::firstOrCreate(['period_id' => $period->id, 'user_id' => $relation->inviter_user_id]);
            $inviterResult->increment('referral_bonus_amount', $amount);
        }

        return $total;
    }

    private function applyTokenSettlementStatuses(SettlementPeriod $period, bool $dryRun): void
    {
        UserPeriodResult::where('period_id', $period->id)->get()->each(function (UserPeriodResult $row) use ($dryRun) {
            $net = (int) $row->reward_amount + (int) $row->referral_bonus_amount - (int) $row->total_entry_amount;
            $side = match (true) {
                $net > 0 => 'creditor',
                $net < 0 => 'debtor',
                default => 'balanced',
            };

            $row->update([
                'final_settlement_amount' => abs($net),
                'settlement_status' => ($dryRun ? 'calculated_' : 'finalized_').$side,
            ]);
        });
    }

    private function writeLedgers(SettlementPeriod $period): void
    {
        FinancialLedger::where('period_id', $period->id)->delete();

        PaymentTransaction::whereHas('predictionEntry', fn ($q) => $q->where('period_id', $period->id))->where('status', 'paid')->each(function ($transaction) use ($period) {
            FinancialLedger::create(['user_id' => $transaction->user_id, 'period_id' => $period->id, 'source_type' => PaymentTransaction::class, 'source_id' => $transaction->id, 'type' => 'token_stake', 'direction' => 'debit', 'amount' => $transaction->entry_amount, 'description' => 'تعهد توکنی شرط']);
        });
    }
}
