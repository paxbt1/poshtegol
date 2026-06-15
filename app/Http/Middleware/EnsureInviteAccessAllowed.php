<?php

namespace App\Http\Middleware;

use App\Services\InviteAccessService;
use App\Services\UnauthorizedAccessService;
use Closure;
use Illuminate\Http\Request;

class EnsureInviteAccessAllowed
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            return $next($request);
        }

        if (app(InviteAccessService::class)->hasValidContext($request)) {
            return $next($request);
        }

        return app(UnauthorizedAccessService::class)->deny();
    }
}
