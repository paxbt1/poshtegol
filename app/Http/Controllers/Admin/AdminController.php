<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FinanceSettingsRequest;
use App\Models\AppSetting;
use App\Models\FootballMatch;
use App\Models\InviteLink;
use App\Models\PaymentTransaction;
use App\Models\PeriodSettlement;
use App\Models\PredictionEntry;
use App\Models\ReferralRelation;
use App\Models\User;
use App\Services\InviteCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index', [
            'usersCount' => User::count(),
            'matchesCount' => FootballMatch::count(),
            'predictionsCount' => PredictionEntry::where('payment_status', 'paid')->count(),
            'activeMatchesCount' => FootballMatch::whereIn('status', ['live_first_half', 'halftime', 'live_second_half'])->count(),
            'totalCollected' => PaymentTransaction::where('status', 'paid')->sum('amount'),
            'totalGatewayFee' => PaymentTransaction::where('status', 'paid')->sum('gateway_fee_amount'),
            'pendingSettlements' => PeriodSettlement::whereIn('status', ['draft', 'calculated'])->count(),
            'recentPayments' => PaymentTransaction::with('user')->latest()->take(5)->get(),
        ]);
    }

    public function page(string $page)
    {
        abort_unless(in_array($page, ['matches', 'users', 'predictions', 'payments', 'settlements', 'referrals'], true), 404);

        return view('admin.'.$page, [
            'users' => User::query()->latest()->take(50)->get(),
            'matches' => FootballMatch::query()->with(['homeTeam', 'awayTeam'])->orderBy('starts_at')->take(120)->get(),
            'predictions' => PredictionEntry::query()->with(['user', 'match.homeTeam', 'match.awayTeam'])->latest()->take(50)->get(),
            'transactions' => PaymentTransaction::query()->with(['user', 'predictionEntry.match.homeTeam', 'predictionEntry.match.awayTeam'])->latest()->take(50)->get(),
            'referrals' => ReferralRelation::query()->with(['inviter', 'referred'])->latest()->take(50)->get(),
            'masterInviteLinks' => InviteLink::query()->with('owner')->where('type', InviteLink::TYPE_MASTER_ACCESS)->latest()->get(),
            'userInviteLinks' => InviteLink::query()->with('owner')->where('type', InviteLink::TYPE_USER_REFERRAL)->latest()->get(),
        ]);
    }

    public function settings()
    {
        return view('admin.settings', [
            'settings' => [
                'app_title' => AppSetting::getValue('app_title', 'پشت گل'),
                'unauthorized_access_mode' => AppSetting::getValue('unauthorized_access_mode', env('UNAUTHORIZED_ACCESS_MODE', 'show_public_homepage')),
                'unauthorized_redirect_url' => AppSetting::getValue('unauthorized_redirect_url', env('UNAUTHORIZED_REDIRECT_URL', 'https://poshtegol.ir')),
                'prediction_lock_minutes' => AppSetting::getValue('prediction_lock_minutes', 60),
                'enable_half_time_markets' => AppSetting::getBool('enable_half_time_markets', false),
                'football_data_enabled' => AppSetting::getBool('football_data_enabled', (bool) config('football-data.enabled')),
                'football_data_base_url' => AppSetting::getValue('football_data_base_url', config('football-data.base_url')),
                'football_data_api_token' => AppSetting::getValue('football_data_api_token', config('football-data.token')),
                'football_data_competition_code' => AppSetting::getValue('football_data_competition_code', config('football-data.competition_code')),
                'football_data_season' => AppSetting::getValue('football_data_season', config('football-data.season')),
                'football_data_timeout' => AppSetting::getValue('football_data_timeout', config('football-data.timeout', 20)),
                'football_data_download_crests' => AppSetting::getBool('football_data_download_crests', (bool) config('football-data.download_crests')),
                'news_enabled' => AppSetting::getBool('news_enabled', (bool) config('news.enabled')),
                'news_provider' => AppSetting::getValue('news_provider', config('news.provider', 'gnews')),
                'gnews_base_url' => AppSetting::getValue('gnews_base_url', config('news.gnews_base_url', 'https://gnews.io/api/v4')),
                'gnews_api_key' => AppSetting::getValue('gnews_api_key', config('news.gnews_api_key', '')),
                'news_query' => AppSetting::getValue('news_query', config('news.query', 'FIFA World Cup 2026 OR World Cup 2026')),
                'news_language' => AppSetting::getValue('news_language', config('news.language', 'en')),
                'news_country' => AppSetting::getValue('news_country', config('news.country', '')),
                'news_max_per_sync' => AppSetting::getValue('news_max_per_sync', config('news.max_per_sync', 8)),
                'news_download_images' => AppSetting::getBool('news_download_images', (bool) config('news.download_images', true)),
                'news_sort_by' => AppSetting::getValue('news_sort_by', config('news.sort_by', 'publishedAt')),
                'news_in_fields' => AppSetting::getValue('news_in_fields', config('news.in_fields', 'title,description')),
                'gemini_api_key' => AppSetting::getValue('gemini_api_key', config('news.gemini_api_key', '')),
                'gemini_model' => AppSetting::getValue('gemini_model', config('news.gemini_model', 'gemini-2.0-flash-lite')),
                'news_translation_provider' => AppSetting::getValue('news_translation_provider', config('news.translation_provider', 'gemini')),
                'microsoft_translator_key' => AppSetting::getValue('microsoft_translator_key', config('news.microsoft_translator_key', '')),
                'microsoft_translator_region' => AppSetting::getValue('microsoft_translator_region', config('news.microsoft_translator_region', '')),
                'microsoft_translator_endpoint' => AppSetting::getValue('microsoft_translator_endpoint', config('news.microsoft_translator_endpoint', 'https://api.cognitive.microsofttranslator.com')),
                'payment_driver' => AppSetting::getValue('payment_driver', config('payment.default', 'zibal')),
                'zibal_merchant_id' => AppSetting::getValue('zibal_merchant_id', env('ZIBAL_MERCHANT_ID', '')),
                'zibal_sandbox' => AppSetting::getBool('zibal_sandbox', (bool) env('ZIBAL_SANDBOX', false)),
                'zibal_callback_url' => AppSetting::getValue('zibal_callback_url', route('payment.callback.zibal')),
                'payment_currency' => AppSetting::getValue('payment_currency', config('payment.drivers.zibal.currency', 'R')),
                'payment_amount_multiplier' => AppSetting::getValue('payment_amount_multiplier', env('PAYMENT_AMOUNT_MULTIPLIER', 10)),
            ],
        ]);
    }

    public function updateGeneralSettings(Request $request)
    {
        $data = $request->validate([
            'app_title' => ['required', 'string', 'max:120'],
            'unauthorized_access_mode' => ['required', Rule::in(['show_public_homepage', 'redirect', '404'])],
            'unauthorized_redirect_url' => ['nullable', 'url', 'max:255'],
            'prediction_lock_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'enable_half_time_markets' => ['nullable', 'boolean'],
        ], [], [
            'app_title' => 'عنوان اپلیکیشن',
            'unauthorized_access_mode' => 'رفتار دسترسی غیرمجاز',
            'unauthorized_redirect_url' => 'آدرس ریدایرکت',
            'prediction_lock_minutes' => 'زمان قفل پیش‌بینی',
        ]);

        AppSetting::setValue('app_title', $data['app_title']);
        AppSetting::setValue('unauthorized_access_mode', $data['unauthorized_access_mode']);
        AppSetting::setValue('unauthorized_redirect_url', $data['unauthorized_redirect_url'] ?: 'https://poshtegol.ir');
        AppSetting::setValue('prediction_lock_minutes', $data['prediction_lock_minutes']);
        AppSetting::setValue('enable_half_time_markets', $request->boolean('enable_half_time_markets'));

        FootballMatch::whereNotNull('starts_at')->get()->each(function (FootballMatch $match) use ($data) {
            $match->prediction_locks_at = $match->starts_at->copy()->subMinutes((int) $data['prediction_lock_minutes']);
            $match->save();
        });

        return response()->json(['message' => 'تنظیمات عمومی ذخیره شد.']);
    }

    public function updateFootballDataSettings(Request $request)
    {
        $data = $request->validate([
            'football_data_enabled' => ['nullable', 'boolean'],
            'football_data_base_url' => ['required', 'url', 'max:255'],
            'football_data_api_token' => ['nullable', 'string', 'max:255'],
            'football_data_competition_code' => ['required', 'string', 'max:20'],
            'football_data_season' => ['required', 'integer', 'min:2026', 'max:2100'],
            'football_data_timeout' => ['required', 'integer', 'min:5', 'max:90'],
            'football_data_download_crests' => ['nullable', 'boolean'],
        ]);

        AppSetting::setValue('football_data_enabled', $request->boolean('football_data_enabled'));
        AppSetting::setValue('football_data_base_url', rtrim($data['football_data_base_url'], '/'));
        AppSetting::setValue('football_data_competition_code', strtoupper($data['football_data_competition_code']));
        AppSetting::setValue('football_data_season', $data['football_data_season']);
        AppSetting::setValue('football_data_timeout', $data['football_data_timeout']);
        AppSetting::setValue('football_data_download_crests', $request->boolean('football_data_download_crests'));

        if (! empty($data['football_data_api_token'])) {
            AppSetting::setValue('football_data_api_token', $data['football_data_api_token'], true);
        }

        return response()->json(['message' => 'تنظیمات وب‌سرویس ذخیره شد.']);
    }


    public function updateNewsSettings(Request $request)
    {
        $data = $request->validate([
            'news_enabled' => ['nullable', 'boolean'],
            'news_provider' => ['required', Rule::in(['gnews'])],
            'gnews_base_url' => ['required', 'url', 'max:255'],
            'gnews_api_key' => ['nullable', 'string', 'max:255'],
            'news_query' => ['required', 'string', 'max:500'],
            'news_language' => ['required', 'string', 'max:8'],
            'news_country' => ['nullable', 'string', 'max:8'],
            'news_max_per_sync' => ['required', 'integer', 'min:1', 'max:25'],
            'news_download_images' => ['nullable', 'boolean'],
            'news_sort_by' => ['required', Rule::in(['publishedAt', 'relevance'])],
            'news_in_fields' => ['nullable', 'string', 'max:120'],
            'gemini_api_key' => ['nullable', 'string', 'max:255'],
            'gemini_model' => ['required', 'string', 'max:80'],
            'news_translation_provider' => ['required', Rule::in(['gemini', 'microsoft'])],
            'microsoft_translator_key' => ['nullable', 'string', 'max:255'],
            'microsoft_translator_region' => ['nullable', 'string', 'max:80'],
            'microsoft_translator_endpoint' => ['required', 'url', 'max:255'],
        ], [], [
            'gnews_api_key' => 'کلید GNews',
            'gemini_api_key' => 'کلید Gemini',
            'microsoft_translator_key' => 'کلید Microsoft Translator',
            'news_query' => 'عبارت جستجوی خبر',
        ]);

        AppSetting::setValue('news_enabled', $request->boolean('news_enabled'));
        AppSetting::setValue('news_provider', 'gnews');
        AppSetting::setValue('gnews_base_url', rtrim($data['gnews_base_url'], '/'));
        AppSetting::setValue('news_query', $data['news_query']);
        AppSetting::setValue('news_language', strtolower($data['news_language']));
        AppSetting::setValue('news_country', strtolower($data['news_country'] ?? ''));
        AppSetting::setValue('news_max_per_sync', $data['news_max_per_sync']);
        AppSetting::setValue('news_download_images', $request->boolean('news_download_images'));
        AppSetting::setValue('news_sort_by', $data['news_sort_by']);
        AppSetting::setValue('news_in_fields', $data['news_in_fields'] ?? 'title,description');
        AppSetting::setValue('gemini_model', $data['gemini_model']);
        AppSetting::setValue('news_translation_provider', $data['news_translation_provider']);
        AppSetting::setValue('microsoft_translator_region', $data['microsoft_translator_region'] ?? '');
        AppSetting::setValue('microsoft_translator_endpoint', rtrim($data['microsoft_translator_endpoint'], '/'));

        if (! empty($data['gnews_api_key'])) {
            AppSetting::setValue('gnews_api_key', $data['gnews_api_key'], true);
        }

        if (! empty($data['gemini_api_key'])) {
            AppSetting::setValue('gemini_api_key', $data['gemini_api_key'], true);
        }

        if (! empty($data['microsoft_translator_key'])) {
            AppSetting::setValue('microsoft_translator_key', $data['microsoft_translator_key'], true);
        }

        return response()->json(['message' => 'تنظیمات اخبار و ترجمه ذخیره شد.']);
    }

    public function updatePaymentGatewaySettings(Request $request)
    {
        $data = $request->validate([
            'payment_driver' => ['required', Rule::in(['zibal'])],
            'zibal_merchant_id' => ['nullable', 'string', 'max:255'],
            'zibal_sandbox' => ['nullable', 'boolean'],
            'zibal_callback_url' => ['required', 'url', 'max:255'],
            'payment_currency' => ['required', Rule::in(['R', 'T'])],
            'payment_amount_multiplier' => ['required', 'integer', 'min:1', 'max:100'],
        ], [], [
            'zibal_merchant_id' => 'مرچنت کد زیبال',
            'zibal_callback_url' => 'آدرس بازگشت زیبال',
        ]);

        AppSetting::setValue('payment_driver', 'zibal');
        AppSetting::setValue('zibal_sandbox', $request->boolean('zibal_sandbox'));
        AppSetting::setValue('zibal_callback_url', $data['zibal_callback_url']);
        AppSetting::setValue('payment_currency', $data['payment_currency']);
        AppSetting::setValue('payment_amount_multiplier', $data['payment_amount_multiplier']);

        if (! empty($data['zibal_merchant_id'])) {
            AppSetting::setValue('zibal_merchant_id', $data['zibal_merchant_id'], true);
        }

        return response()->json(['message' => 'تنظیمات درگاه پرداخت ذخیره شد.']);
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'is_admin' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ], [], [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'password' => 'رمز عبور جدید',
        ]);

        $isSelf = $request->user()->id === $user->id;
        $isActive = $request->boolean('is_active');
        $isAdmin = $request->boolean('is_admin');

        if ($isSelf && (! $isActive || ! $isAdmin)) {
            return response()->json(['message' => 'برای جلوگیری از قفل شدن پنل، نمی‌توانید دسترسی مدیر فعلی خودتان را حذف یا غیرفعال کنید.'], 422);
        }

        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->is_active = $isActive;
        $user->is_admin = $isAdmin;

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json(['message' => 'کاربر به‌روزرسانی شد.']);
    }

    public function storeInvite(Request $request, InviteCodeGenerator $generator)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in([InviteLink::TYPE_MASTER_ACCESS, InviteLink::TYPE_USER_REFERRAL])],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $isReferral = $data['type'] === InviteLink::TYPE_USER_REFERRAL;
        if ($isReferral && empty($data['owner_user_id'])) {
            return response()->json(['message' => 'برای لینک دعوت کاربر، مالک لینک را انتخاب کنید.', 'errors' => ['owner_user_id' => ['مالک لینک را انتخاب کنید.']]], 422);
        }

        InviteLink::create([
            'code' => $generator->make(),
            'owner_user_id' => $isReferral ? $data['owner_user_id'] : ($data['owner_user_id'] ?? null),
            'type' => $data['type'],
            'title' => $data['title'] ?? ($isReferral ? 'لینک دعوت کاربر' : 'لینک دسترسی مادر'),
            'is_active' => true,
            'earns_commission' => $isReferral,
            'max_uses' => $data['max_uses'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return response()->json(['message' => 'لینک دعوت ساخته شد.', 'redirect' => route('admin.referrals')]);
    }

    public function updateInvite(Request $request, InviteLink $invite)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $invite->update([
            'title' => $data['title'] ?? $invite->title,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : false,
            'max_uses' => $data['max_uses'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'earns_commission' => $invite->type === InviteLink::TYPE_USER_REFERRAL,
        ]);

        return response()->json(['message' => 'لینک دعوت به‌روزرسانی شد.']);
    }

    public function destroyInvite(InviteLink $invite)
    {
        if ($invite->used_count > 0) {
            return response()->json(['message' => 'لینک استفاده‌شده قابل حذف نیست.'], 422);
        }

        $invite->delete();

        return response()->json(['message' => 'لینک دعوت حذف شد.', 'redirect' => route('admin.referrals')]);
    }

    public function finance()
    {
        $paid = PaymentTransaction::where('status', 'paid');

        return view('admin.finance', [
            'gatewayFees' => (clone $paid)->sum('gateway_fee_amount'),
            'poolAmount' => (clone $paid)->sum('entry_amount'),
            'paidAmount' => (clone $paid)->sum('amount'),
            'paidCount' => (clone $paid)->count(),
            'needsReviewCount' => PaymentTransaction::where('status', 'needs_review')->count(),
            'settings' => [
                'gateway_fee_percent' => AppSetting::getValue('gateway_fee_percent', env('GATEWAY_FEE_PERCENT', 10)),
                'referral_rate' => AppSetting::getValue('referral_rate', 3),
                'referral_enabled_until_group_stage' => AppSetting::getBool('referral_enabled_until_group_stage', true),
                'group_entry_amount' => FootballMatch::where('stage', 'group')->value('entry_amount') ?? 50000,
                'round32_entry_amount' => FootballMatch::where('stage', 'round_32')->value('entry_amount') ?? 75000,
                'round16_entry_amount' => FootballMatch::where('stage', 'round_16')->value('entry_amount') ?? 85000,
                'quarter_final_entry_amount' => FootballMatch::where('stage', 'quarter_final')->value('entry_amount') ?? 100000,
                'semi_final_entry_amount' => FootballMatch::where('stage', 'semi_final')->value('entry_amount') ?? 125000,
                'bronze_final_entry_amount' => FootballMatch::where('stage', 'bronze_final')->value('entry_amount') ?? 125000,
                'final_entry_amount' => FootballMatch::where('stage', 'final')->value('entry_amount') ?? 50000,
            ],
        ]);
    }

    public function updateFinance(FinanceSettingsRequest $request)
    {
        $data = $request->validated();

        AppSetting::setValue('gateway_fee_percent', $data['gateway_fee_percent']);
        AppSetting::setValue('referral_rate', $data['referral_rate']);
        AppSetting::setValue('referral_enabled_until_group_stage', $request->boolean('referral_enabled_until_group_stage'));

        FootballMatch::where('stage', 'group')->update(['entry_amount' => $data['group_entry_amount']]);
        FootballMatch::where('stage', 'round_32')->update(['entry_amount' => $data['round32_entry_amount']]);
        FootballMatch::where('stage', 'round_16')->update(['entry_amount' => $data['round16_entry_amount']]);
        FootballMatch::where('stage', 'quarter_final')->update(['entry_amount' => $data['quarter_final_entry_amount']]);
        FootballMatch::where('stage', 'semi_final')->update(['entry_amount' => $data['semi_final_entry_amount']]);
        FootballMatch::where('stage', 'bronze_final')->update(['entry_amount' => $data['bronze_final_entry_amount']]);
        FootballMatch::where('stage', 'final')->update(['entry_amount' => $data['final_entry_amount']]);

        return response()->json(['message' => 'تنظیمات مالی ذخیره شد.']);
    }
}
