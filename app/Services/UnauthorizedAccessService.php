<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Services\PublicPortal\PublicSiteData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class UnauthorizedAccessService
{
    public function deny(): Response|RedirectResponse
    {
        $mode = Schema::hasTable('app_settings')
            ? (AppSetting::where('key', 'unauthorized_access_mode')->value('value') ?: 'show_public_homepage')
            : 'show_public_homepage';

        if ($mode === '404') {
            abort(404);
        }

        if ($mode === 'redirect') {
            $url = Schema::hasTable('app_settings')
                ? (AppSetting::where('key', 'unauthorized_redirect_url')->value('value') ?: 'https://poshtegol.ir')
                : 'https://poshtegol.ir';

            return redirect()->away($url);
        }

        return response()->view('public.home', app(PublicSiteData::class)->home());
    }
}
