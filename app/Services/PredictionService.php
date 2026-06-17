<?php

namespace App\Services;

use App\Models\FootballMatch;
use App\Models\PaymentTransaction;
use App\Models\PredictionEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PredictionService
{
    public function __construct(
        private readonly MatchLockService $lockService,
    ) {}

    public function calculateAmounts(FootballMatch $match, ?int $stakeTokens = null): array
    {
        $tokens = max(50, (int) ($stakeTokens ?: 50));

        return [
            'entry_amount' => $tokens,
            'gateway_fee_amount' => 0,
            'payable_amount' => $tokens,
        ];
    }

    public function createOrUpdateDraftPrediction(User $user, FootballMatch $match, array $data): PredictionEntry
    {
        if (! $this->lockService->canPredict($match)) {
            throw ValidationException::withMessages(['match' => $this->lockService->reason($match)]);
        }

        $tokens = max(50, (int) ($data['stake_tokens'] ?? 50));
        $amounts = $this->calculateAmounts($match, $tokens);

        return DB::transaction(function () use ($user, $match, $data, $tokens, $amounts) {
            $entry = PredictionEntry::query()
                ->where('user_id', $user->id)
                ->where('match_id', $match->id)
                ->whereIn('payment_status', ['paid', 'needs_review', 'paid_but_locked', 'unpaid', 'pending', 'pending_review', 'failed', 'cancelled'])
                ->latest('id')
                ->first() ?? new PredictionEntry([
                    'user_id' => $user->id,
                    'match_id' => $match->id,
                ]);

            $entry->fill(array_merge($amounts, [
                'period_id' => $match->period_id,
                'full_time_result' => $data['full_time_result'],
                'exact_home_score' => $data['exact_home_score'],
                'exact_away_score' => $data['exact_away_score'],
                'total_goals_option' => $data['total_goals_option'],
                'qualified_team_id' => $data['qualified_team_id'] ?? null,
                'payment_status' => 'paid',
                'prediction_status' => 'locked',
                'paid_at' => $entry->paid_at ?: now(),
                'locked_at' => now(),
                'cancelled_at' => null,
            ]));
            $entry->save();

            PaymentTransaction::updateOrCreate(
                [
                    'prediction_entry_id' => $entry->id,
                    'gateway' => 'token',
                ],
                [
                    'user_id' => $user->id,
                    'amount' => $tokens,
                    'amount_gateway' => $tokens,
                    'entry_amount' => $tokens,
                    'gateway_fee_amount' => 0,
                    'transaction_id' => 'token-'.$entry->id,
                    'reference_id' => 'token-'.$entry->id,
                    'status' => 'paid',
                    'request_payload' => [
                        'stake_tokens' => $tokens,
                        'updated_at' => now()->toIso8601String(),
                    ],
                    'callback_payload' => null,
                    'paid_at' => now(),
                    'verified_at' => now(),
                ],
            );

            PredictionEntry::where('user_id', $entry->user_id)
                ->where('match_id', $entry->match_id)
                ->where('id', '!=', $entry->id)
                ->whereIn('payment_status', ['unpaid', 'pending', 'pending_review', 'failed'])
                ->update(['payment_status' => 'cancelled', 'prediction_status' => 'cancelled', 'cancelled_at' => now()]);

            return $entry->refresh();
        });
    }

    public function finalizeAfterPayment(PredictionEntry $entry): PredictionEntry
    {
        $entry->update([
            'payment_status' => 'paid',
            'prediction_status' => 'locked',
            'paid_at' => now(),
            'locked_at' => now(),
        ]);

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
