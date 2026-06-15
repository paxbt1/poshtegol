<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\InviteLink;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        AppSetting::setValue('app_title', env('APP_PUBLIC_TITLE', 'کاپ خانوادگی جام جهانی ۲۰۲۶'));
        AppSetting::setValue('public_site_name', env('PUBLIC_SITE_NAME', 'پشت گل'));
        AppSetting::setValue('public_site_tagline', env('PUBLIC_SITE_TAGLINE', 'خبر، نتیجه زنده و روایت فارسی فوتبال جهان'));
        AppSetting::setValue('public_site_domain', env('PUBLIC_SITE_DOMAIN', 'poshtegol.ir'));
        AppSetting::setValue('public_ads_enabled', env('PUBLIC_ADS_ENABLED', true));
        AppSetting::setValue('gateway_fee_percent', env('GATEWAY_FEE_PERCENT', 10));
        AppSetting::setValue('referral_rate', env('REFERRAL_RATE', 3));
        AppSetting::setValue('referral_enabled_until_group_stage', true);
        AppSetting::setValue('prediction_lock_minutes', 60);
        AppSetting::setValue('enable_half_time_markets', false);
        AppSetting::setValue('unauthorized_access_mode', env('UNAUTHORIZED_ACCESS_MODE', 'show_public_homepage'));
        AppSetting::setValue('unauthorized_redirect_url', env('UNAUTHORIZED_REDIRECT_URL', 'https://poshtegol.ir'));
        AppSetting::setValue('football_data_enabled', env('FOOTBALL_DATA_ENABLED', true));
        AppSetting::setValue('football_data_base_url', env('FOOTBALL_DATA_BASE_URL', 'https://api.football-data.org/v4'));
        AppSetting::setValue('football_data_competition_code', env('FOOTBALL_DATA_COMPETITION_CODE', 'WC'));
        AppSetting::setValue('football_data_season', env('FOOTBALL_DATA_SEASON', 2026));
        AppSetting::setValue('football_data_timeout', env('FOOTBALL_DATA_TIMEOUT', 20));
        AppSetting::setValue('football_data_download_crests', env('FOOTBALL_DATA_DOWNLOAD_CRESTS', true));
        AppSetting::setValue('news_enabled', env('NEWS_ENABLED', false));
        AppSetting::setValue('news_provider', env('NEWS_PROVIDER', 'gnews'));
        AppSetting::setValue('gnews_base_url', env('GNEWS_BASE_URL', 'https://gnews.io/api/v4'));
        AppSetting::setValue('news_query', env('NEWS_QUERY', 'FIFA World Cup 2026 OR World Cup 2026'));
        AppSetting::setValue('news_language', env('NEWS_LANGUAGE', 'en'));
        AppSetting::setValue('news_country', env('NEWS_COUNTRY', ''));
        AppSetting::setValue('news_max_per_sync', env('NEWS_MAX_PER_SYNC', 8));
        AppSetting::setValue('news_sort_by', env('NEWS_SORT_BY', 'publishedAt'));
        AppSetting::setValue('news_in_fields', env('NEWS_IN_FIELDS', 'title,description'));
        AppSetting::setValue('news_translation_provider', env('NEWS_TRANSLATION_PROVIDER', 'gemini'));
        AppSetting::setValue('gemini_model', env('GEMINI_MODEL', 'gemini-2.0-flash-lite'));
        AppSetting::setValue('llm7_base_url', env('LLM7_BASE_URL', 'https://api.llm7.io/v1'));
        AppSetting::setValue('llm7_model', env('LLM7_MODEL', 'default'));
        AppSetting::setValue('microsoft_translator_region', env('MICROSOFT_TRANSLATOR_REGION', ''));
        AppSetting::setValue('microsoft_translator_endpoint', env('MICROSOFT_TRANSLATOR_ENDPOINT', 'https://api.cognitive.microsofttranslator.com'));
        if (env('GNEWS_API_KEY')) {
            AppSetting::setValue('gnews_api_key', env('GNEWS_API_KEY'), true);
        }
        if (env('GEMINI_API_KEY')) {
            AppSetting::setValue('gemini_api_key', env('GEMINI_API_KEY'), true);
        }
        if (env('LLM7_API_KEY')) {
            AppSetting::setValue('llm7_api_key', env('LLM7_API_KEY'), true);
        }
        if (env('MICROSOFT_TRANSLATOR_KEY')) {
            AppSetting::setValue('microsoft_translator_key', env('MICROSOFT_TRANSLATOR_KEY'), true);
        }
        if (env('FOOTBALL_DATA_API_TOKEN')) {
            AppSetting::setValue('football_data_api_token', env('FOOTBALL_DATA_API_TOKEN'), true);
        }

        $admin = null;
        $adminMobile = env('POSHTEGOL_ADMIN_MOBILE') ?: env('FAMILY_CUP_ADMIN_MOBILE');
        $adminPassword = env('POSHTEGOL_ADMIN_PASSWORD') ?: env('FAMILY_CUP_ADMIN_PASSWORD');

        if (! $adminMobile && app()->environment(['local', 'testing'])) {
            $adminMobile = '09120000000';
        }

        if ($adminMobile) {
            if (! $adminPassword && app()->environment(['local', 'testing'])) {
                $adminPassword = 'Admin@2026';
                Log::warning('رمز ادمین تنظیم نشده است؛ رمز پیش‌فرض فقط برای محیط توسعه استفاده شد.', [
                    'mobile' => $adminMobile,
                    'password' => $adminPassword,
                ]);
            }

            $admin = User::updateOrCreate(
                ['mobile' => $adminMobile],
                [
                    'first_name' => env('POSHTEGOL_ADMIN_FIRST_NAME', env('FAMILY_CUP_ADMIN_FIRST_NAME', 'مدیر')),
                    'last_name' => env('POSHTEGOL_ADMIN_LAST_NAME', env('FAMILY_CUP_ADMIN_LAST_NAME', 'پشت گل')),
                    'password' => $adminPassword ? Hash::make($adminPassword) : null,
                    'invite_code' => 'ADMIN2026',
                    'is_admin' => true,
                    'is_active' => true,
                    'mobile_verified_at' => now(),
                ],
            );
        }

        $this->call(Fifa2026Seeder::class);

        InviteLink::updateOrCreate(
            ['code' => env('FAMILY_CUP_MASTER_INVITE_CODE', 'FAMILY2026')],
            [
                'owner_user_id' => $admin?->id,
                'type' => InviteLink::TYPE_MASTER_ACCESS,
                'title' => 'لینک دسترسی مادر',
                'is_active' => true,
                'earns_commission' => false,
                'max_uses' => null,
            ],
        );
    }
}
