<?php

namespace App\Services;

use App\Models\InviteLink;
use App\Models\User;

class ReferralService
{
    public function __construct(private readonly InviteCodeGenerator $inviteCodeGenerator) {}

    public function ensureInviteCode(User $user): string
    {
        $invite = InviteLink::firstOrCreate(
            ['owner_user_id' => $user->id, 'type' => InviteLink::TYPE_USER_REFERRAL],
            [
                'code' => $user->invite_code ?: $this->inviteCodeGenerator->make(),
                'title' => 'لینک دعوت '.$user->full_name,
                'is_active' => true,
                'earns_commission' => true,
            ],
        );

        if ($user->invite_code !== $invite->code) {
            $user->forceFill(['invite_code' => $invite->code])->save();
        }

        return $invite->code;
    }
}
