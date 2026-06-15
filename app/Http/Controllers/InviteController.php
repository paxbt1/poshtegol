<?php

namespace App\Http\Controllers;

use App\Services\ReferralService;

class InviteController extends Controller
{
    public function index(ReferralService $referralService)
    {
        $user = auth()->user();
        $referralService->ensureInviteCode($user);

        return view('invite.index', [
            'inviteUrl' => route('join', $user->invite_code),
            'invitedUsers' => $user->invitedUsers()->withCount(['predictionEntries as paid_predictions_count' => fn ($query) => $query->where('payment_status', 'paid')])->latest()->get(),
        ]);
    }
}
