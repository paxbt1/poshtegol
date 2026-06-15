<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdSlot;
use App\Models\AppSetting;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\NewsSource;
use App\Models\NewsSyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class NewsAdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.news-site-dashboard', [
            'articlesCount' => Schema::hasTable('news_articles') ? NewsArticle::count() : 0,
            'publishedCount' => Schema::hasTable('news_articles') ? NewsArticle::where('status', 'published')->count() : 0,
            'categoriesCount' => Schema::hasTable('news_categories') ? NewsCategory::where('is_active', true)->count() : 0,
            'activeAdsCount' => Schema::hasTable('ads') ? Ad::where('is_active', true)->count() : 0,
            'activeSourcesCount' => Schema::hasTable('news_sources') ? NewsSource::where('is_active', true)->count() : 0,
            'latestArticles' => Schema::hasTable('news_articles') ? NewsArticle::with('category')->latest('published_at')->take(8)->get() : collect(),
            'latestLogs' => Schema::hasTable('news_sync_logs') ? NewsSyncLog::latest()->take(6)->get() : collect(),
        ]);
    }

    public function settings()
    {
        return view('admin.news-site-settings', [
            'settings' => [
                'public_site_name' => AppSetting::getValue('public_site_name', 'پشت گل'),
                'public_site_tagline' => AppSetting::getValue('public_site_tagline', 'خبر، نتیجه زنده و روایت فارسی فوتبال جهان'),
                'public_site_domain' => AppSetting::getValue('public_site_domain', 'poshtegol.ir'),
                'public_ads_enabled' => AppSetting::getBool('public_ads_enabled', true),
                'news_enabled' => AppSetting::getBool('news_enabled', (bool) config('news.enabled')),
                'news_provider' => AppSetting::getValue('news_provider', config('news.provider', 'gnews')),
                'gnews_base_url' => AppSetting::getValue('gnews_base_url', config('news.gnews_base_url', 'https://gnews.io/api/v4')),
                'gnews_api_key' => AppSetting::getValue('gnews_api_key', config('news.gnews_api_key', '')),
                'news_query' => AppSetting::getValue('news_query', config('news.query', 'FIFA World Cup 2026 OR football OR soccer')),
                'news_category_queries_json' => AppSetting::getValue('news_category_queries_json', $this->defaultCategoryQueriesJson()),
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
                'footer_about_text' => AppSetting::getValue('footer_about_text', 'پشت گل یک رسانه فارسی فوتبال است که اخبار خارجی را با لینک منبع اصلی بازنشر و خلاصه‌سازی می‌کند.'),
                'footer_column_1_title' => AppSetting::getValue('footer_column_1_title', 'بخش‌ها'),
                'footer_column_1_body' => AppSetting::getValue('footer_column_1_body', "اخبار\nنتایج زنده\nبرنامه بازی‌ها\nویدئوها"),
                'footer_column_2_title' => AppSetting::getValue('footer_column_2_title', 'تبلیغات و همکاری'),
                'footer_column_2_body' => AppSetting::getValue('footer_column_2_body', 'برای رزرو بنرهای تبلیغاتی و همکاری رسانه‌ای با مدیر سایت هماهنگ کنید.'),
                'footer_column_3_title' => AppSetting::getValue('footer_column_3_title', 'حقوق محتوا'),
                'footer_column_3_body' => AppSetting::getValue('footer_column_3_body', 'تمام اخبار از منابع خارجی دریافت شده و لینک منبع اصلی در انتهای خبر درج می‌شود.'),
                'footer_copyright_text' => AppSetting::getValue('footer_copyright_text', '© پشت گل - تمامی حقوق قالب و گردآوری محفوظ است.'),
            ],
            'sources' => Schema::hasTable('news_sources') ? NewsSource::orderBy('priority')->get() : collect(),
            'categories' => Schema::hasTable('news_categories') ? NewsCategory::where('is_active', true)->orderBy('sort_order')->get() : collect(),
        ]);
    }

    public function updateSiteSettings(Request $request)
    {
        $data = $request->validate([
            'public_site_name' => ['required', 'string', 'max:120'],
            'public_site_tagline' => ['required', 'string', 'max:180'],
            'public_site_domain' => ['required', 'string', 'max:120'],
            'public_ads_enabled' => ['nullable', 'boolean'],
        ]);

        foreach (['public_site_name', 'public_site_tagline', 'public_site_domain'] as $key) {
            AppSetting::setValue($key, $data[$key]);
        }
        AppSetting::setValue('public_ads_enabled', $request->boolean('public_ads_enabled'));

        return response()->json(['message' => 'تنظیمات هویتی سایت خبری ذخیره شد.']);
    }

    public function updateNewsSettings(Request $request)
    {
        $data = $request->validate([
            'news_enabled' => ['nullable', 'boolean'],
            'news_provider' => ['required', Rule::in(['gnews'])],
            'gnews_base_url' => ['required', 'url', 'max:255'],
            'gnews_api_key' => ['nullable', 'string', 'max:255'],
            'news_query' => ['required', 'string', 'max:500'],
            'news_category_queries_json' => ['nullable', 'string', 'max:6000'],
            'news_language' => ['required', 'string', 'max:8'],
            'news_country' => ['nullable', 'string', 'max:8'],
            'news_max_per_sync' => ['required', 'integer', 'min:1', 'max:50'],
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
            'news_category_queries_json' => 'مپ جستجوی دسته‌بندی‌ها',
        ]);

        if (! empty($data['news_category_queries_json'])) {
            json_decode($data['news_category_queries_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['message' => 'مپ جستجوی دسته‌بندی‌ها باید JSON معتبر باشد.', 'errors' => ['news_category_queries_json' => ['JSON معتبر نیست.']]], 422);
            }
        }

        AppSetting::setValue('news_enabled', $request->boolean('news_enabled'));
        AppSetting::setValue('news_provider', 'gnews');
        AppSetting::setValue('gnews_base_url', rtrim($data['gnews_base_url'], '/'));
        AppSetting::setValue('news_query', $data['news_query']);
        AppSetting::setValue('news_category_queries_json', $data['news_category_queries_json'] ?: $this->defaultCategoryQueriesJson());
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

        foreach (['gnews_api_key', 'gemini_api_key', 'microsoft_translator_key'] as $secretKey) {
            if (! empty($data[$secretKey])) {
                AppSetting::setValue($secretKey, $data[$secretKey], true);
            }
        }

        return response()->json(['message' => 'تنظیمات وب‌سرویس‌های خبری و مترجم ذخیره شد.']);
    }

    public function updateFooterSettings(Request $request)
    {
        $data = $request->validate([
            'footer_about_text' => ['nullable', 'string', 'max:1200'],
            'footer_column_1_title' => ['nullable', 'string', 'max:120'],
            'footer_column_1_body' => ['nullable', 'string', 'max:1500'],
            'footer_column_2_title' => ['nullable', 'string', 'max:120'],
            'footer_column_2_body' => ['nullable', 'string', 'max:1500'],
            'footer_column_3_title' => ['nullable', 'string', 'max:120'],
            'footer_column_3_body' => ['nullable', 'string', 'max:1500'],
            'footer_copyright_text' => ['nullable', 'string', 'max:600'],
        ]);

        foreach ($data as $key => $value) {
            AppSetting::setValue($key, $value ?? '');
        }

        return response()->json(['message' => 'محتوای فوتر ذخیره شد.']);
    }

    private function defaultCategoryQueriesJson(): string
    {
        return json_encode([
            'world-cup-2026' => 'FIFA World Cup 2026 OR World Cup 2026',
            'world-football' => 'football OR soccer OR UEFA OR Premier League OR Champions League',
            'iran-asia' => 'Iran football OR AFC football OR Asian football',
            'transfers' => 'football transfer OR signing OR contract',
            'analysis' => 'football analysis OR tactics OR stats',
            'videos' => 'football video OR highlights OR interview',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
