<?php

namespace App\Services;

use App\Models\FootballMatch;
use App\Models\PredictionEntry;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PredictionService
{
    public function __construct(
        private readonly MatchLockService $lockService,
        private readonly PaymentAmountService $amountService,
    ) {}

    public function calculateAmounts(FootballMatch $match): array
    {
        $entryAmount = (int) ($match->entry_amount ?: 50000);
        $gatewayFee = $this->amountService->gatewayFee($entryAmount);

        return [
            'entry_amount' => $entryAmount,
            'gateway_fee_amount' => $gatewayFee,
            'payable_amount' => $entryAmount + $gatewayFee,
        ];
    }

    public function createOrUpdateDraftPrediction(User $user, FootballMatch $match, array $data): PredictionEntry
    {
        if (! $this->lockService->canPredict($match)) {
            throw ValidationException::withMessages(['match' => $this->lockService->reason($match)]);
        }

        if ($this->paidEntry($user, $match)) {
            throw ValidationException::withMessages(['match' => 'پیش‌بینی پرداخت‌شده قابل تغییر نیست.']);
        }

        $amounts = $this->calculateAmounts($match);

        return PredictionEntry::updateOrCreate(
            [
                'user_id' => $user->id,
                'match_id' => $match->id,
                'payment_status' => 'unpaid',
            ],
            array_merge($amounts, [
                'period_id' => $match->period_id,
                'full_time_result' => $data['full_time_result'],
                'exact_home_score' => $data['exact_home_score'],
                'exact_away_score' => $data['exact_away_score'],
                'total_goals_option' => $data['total_goals_option'],
                'qualified_team_id' => $data['qualified_team_id'] ?? null,
                'prediction_status' => 'draft',
                'cancelled_at' => null,
            ]),
        );
    }

    public function finalizeAfterPayment(PredictionEntry $entry): PredictionEntry
    {
        $entry->update([
            'payment_status' => 'paid',
            'prediction_status' => 'locked',
            'paid_at' => now(),
            'locked_at' => now(),
        ]);

        PredictionEntry::where('user_id', $entry->user_id)
            ->where('match_id', $entry->match_id)
            ->where('id', '!=', $entry->id)
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->update(['payment_status' => 'cancelled', 'prediction_status' => 'cancelled', 'cancelled_at' => now()]);

        return $entry->refresh();
    }

    public function paidEntry(User $user, FootballMatch $match): ?PredictionEntry
    {
        return PredictionEntry::where('user_id', $user->id)
            ->where('match_id', $match->id)
            ->whereIn('payment_status', ['paid', 'needs_review', 'paid_but_locked'])
            ->first();
    }
}
