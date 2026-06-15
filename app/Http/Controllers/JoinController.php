<?php

namespace App\Http\Controllers;

use App\Services\InviteAccessService;
use App\Services\UnauthorizedAccessService;
use Illuminate\Http\Request;

class JoinController extends Controller
{
    public function __invoke(string $code, Request $request, InviteAccessService $inviteAccess, UnauthorizedAccessService $unauthorizedAccess)
    {
        $invite = $inviteAccess->findUsable($code);

        if (! $invite) {
            return $unauthorizedAccess->deny();
        }

        $inviteAccess->storeContext($invite, $request);

        return redirect()->route('login');
    }
}
