<?php

namespace App\Services;

use App\Models\InviteLink;
use App\Models\ReferralRelation;
use App\Models\ReferralVisit;
use App\Models\SettlementPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InviteAccessService
{
    public function findUsable(string $code): ?InviteLink
    {
        $invite = InviteLink::where('code', $code)->first();

        return $invite?->isUsable() ? $invite : null;
    }

    public function storeContext(InviteLink $invite, Request $request): void
    {
        $request->session()->put([
            'invite_link_id' => $invite->id,
            'invite_code' => $invite->code,
            'invite_type' => $invite->type,
            'invite_owner_user_id' => $invite->owner_user_id,
            'invite_earns_commission' => $invite->earns_commission,
        ]);

        ReferralVisit::create([
            'inviter_user_id' => $invite->type === InviteLink::TYPE_USER_REFERRAL ? $invite->owner_user_id : null,
            'invite_code' => $invite->code,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }

    public function hasValidContext(Request $request): bool
    {
        $id = $request->session()->get('invite_link_id');
        $code = $request->session()->get('invite_code');

        if (! $id || ! $code) {
            return false;
        }

        $invite = InviteLink::find($id);

        return $invite && $invite->code === $code && $invite->isUsable();
    }

    public function requireContext(Request $request): InviteLink
    {
        $invite = InviteLink::find($request->session()->get('invite_link_id'));

        if (! $invite || $invite->code !== $request->session()->get('invite_code') || ! $invite->isUsable()) {
            abort(404);
        }

        return $invite;
    }

    public function completeRegistration(User $user, Request $request): void
    {
        $invite = $this->requireContext($request);

        DB::transaction(function () use ($user, $request, $invite) {
            $referrerId = null;

            if ($invite->type === InviteLink::TYPE_USER_REFERRAL && $invite->owner_user_id && $invite->owner_user_id !== $user->id) {
                $referrerId = $invite->owner_user_id;
            }

            $user->forceFill([
                'registered_via_invite_code' => $invite->code,
                'registered_via_invite_type' => $invite->type,
                'direct_referrer_user_id' => $referrerId,
                'invited_by_user_id' => $referrerId,
            ])->save();

            if ($referrerId && ! ReferralRelation::where('referred_user_id', $user->id)->exists()) {
                ReferralRelation::create([
                    'inviter_user_id' => $referrerId,
                    'referred_user_id' => $user->id,
                    'source' => 'invite_link',
                    'active_until' => SettlementPeriod::where('type', 'group_stage')->value('ends_at'),
                ]);
            }

            $invite->increment('used_count');

            ReferralVisit::where('invite_code', $invite->code)
                ->whereNull('converted_user_id')
                ->latest()
                ->first()
                ?->update(['converted_user_id' => $user->id, 'converted_at' => now()]);
        });

        $this->clearContext($request);
    }

    public function clearContext(Request $request): void
    {
        $request->session()->forget([
            'invite_link_id',
            'invite_code',
            'invite_type',
            'invite_owner_user_id',
            'invite_earns_commission',
        ]);
    }
}
