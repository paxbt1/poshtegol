<?php

use App\Models\AdSlot;
use App\Models\AppSetting;
use App\Models\NewsSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ads')) {
            Schema::table('ads', function (Blueprint $table) {
                if (! Schema::hasColumn('ads', 'body_text')) {
                    $table->text('body_text')->nullable()->after('title');
                }
                if (! Schema::hasColumn('ads', 'cta_text')) {
                    $table->string('cta_text', 80)->nullable()->after('body_text');
                }
            });
        }

        if (Schema::hasTable('ad_slots')) {
            AdSlot::query()->updateOrCreate(['key' => 'header_under_nav'], ['title' => 'زیر منوی اصلی', 'placement' => 'header', 'device' => 'all', 'width' => 1200, 'height' => 120, 'is_active' => true, 'sort_order' => 10]);
            AdSlot::query()->updateOrCreate(['key' => 'home_top'], ['title' => 'بالای صفحه اصلی', 'placement' => 'home', 'device' => 'all', 'width' => 1200, 'height' => 160, 'is_active' => true, 'sort_order' => 20]);
            AdSlot::query()->updateOrCreate(['key' => 'home_middle'], ['title' => 'میانه صفحه اصلی', 'placement' => 'home', 'device' => 'all', 'width' => 1200, 'height' => 160, 'is_active' => true, 'sort_order' => 30]);
            AdSlot::query()->updateOrCreate(['key' => 'sidebar_top'], ['title' => 'بالای ستون کناری', 'placement' => 'sidebar', 'device' => 'desktop', 'width' => 320, 'height' => 280, 'is_active' => true, 'sort_order' => 40]);
            AdSlot::query()->updateOrCreate(['key' => 'article_top'], ['title' => 'بالای خبر', 'placement' => 'article', 'device' => 'all', 'width' => 1200, 'height' => 140, 'is_active' => true, 'sort_order' => 50]);
            AdSlot::query()->updateOrCreate(['key' => 'article_middle'], ['title' => 'وسط متن خبر', 'placement' => 'article', 'device' => 'all', 'width' => 900, 'height' => 140, 'is_active' => true, 'sort_order' => 60]);
            AdSlot::query()->updateOrCreate(['key' => 'article_bottom'], ['title' => 'پایین خبر اصلی', 'placement' => 'article', 'device' => 'all', 'width' => 1200, 'height' => 160, 'is_active' => true, 'sort_order' => 70]);
            foreach (range(1, 10) as $i) {
                AdSlot::query()->updateOrCreate(['key' => 'article_after_'.$i], [
                    'title' => 'تبلیغ انتهای خبر '.$i,
                    'placement' => 'article_after_content',
                    'device' => 'all',
                    'width' => $i % 2 === 0 ? 580 : 1200,
                    'height' => $i % 2 === 0 ? 180 : 140,
                    'is_active' => true,
                    'sort_order' => 80 + $i,
                ]);
            }
            AdSlot::query()->where('key', 'mobile_sticky_bottom')->update(['is_active' => false]);
        }

        if (Schema::hasTable('app_settings')) {
            AppSetting::setValue('public_site_name', AppSetting::getValue('public_site_name', 'پشت گل'));
            AppSetting::setValue('public_site_tagline', AppSetting::getValue('public_site_tagline', 'خبر، نتیجه زنده و روایت فارسی فوتبال جهان'));
            AppSetting::setValue('public_site_domain', AppSetting::getValue('public_site_domain', 'poshtegol.ir'));
            AppSetting::setValue('footer_about_text', AppSetting::getValue('footer_about_text', 'پشت گل یک رسانه فارسی فوتبال است که اخبار خارجی را با لینک منبع اصلی بازنشر و خلاصه‌سازی می‌کند.'));
            AppSetting::setValue('footer_column_1_title', AppSetting::getValue('footer_column_1_title', 'بخش‌ها'));
            AppSetting::setValue('footer_column_1_body', AppSetting::getValue('footer_column_1_body', "اخبار\nنتایج زنده\nبرنامه بازی‌ها\nویدئوها"));
            AppSetting::setValue('footer_column_2_title', AppSetting::getValue('footer_column_2_title', 'تبلیغات و همکاری'));
            AppSetting::setValue('footer_column_2_body', AppSetting::getValue('footer_column_2_body', 'برای رزرو بنرهای تبلیغاتی و همکاری رسانه‌ای با مدیر سایت هماهنگ کنید.'));
            AppSetting::setValue('footer_column_3_title', AppSetting::getValue('footer_column_3_title', 'حقوق محتوا'));
            AppSetting::setValue('footer_column_3_body', AppSetting::getValue('footer_column_3_body', 'تمام اخبار از منابع خارجی دریافت شده و لینک منبع اصلی در انتهای هر خبر درج می‌شود.'));
            AppSetting::setValue('footer_copyright_text', AppSetting::getValue('footer_copyright_text', '© پشت گل - تمامی حقوق قالب و گردآوری محفوظ است.'));
            AppSetting::setValue('news_category_queries_json', AppSetting::getValue('news_category_queries_json', json_encode([
                'world-cup-2026' => 'FIFA World Cup 2026 OR World Cup 2026',
                'world-football' => 'football OR soccer OR UEFA OR Premier League OR Champions League',
                'iran-asia' => 'Iran football OR AFC football OR Asian football',
                'transfers' => 'football transfer OR signing OR contract',
                'analysis' => 'football analysis OR tactics OR stats',
                'videos' => 'football video OR highlights OR interview',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)));
        }

        if (Schema::hasTable('news_sources')) {
            $sources = [
                ['key' => 'gnews', 'name' => 'GNews', 'type' => 'news', 'requires_key' => true, 'is_active' => true, 'priority' => 10, 'settings' => ['key_setting' => 'gnews_api_key', 'implemented' => true]],
                ['key' => 'newsapi', 'name' => 'NewsAPI.org', 'type' => 'news', 'requires_key' => true, 'is_active' => false, 'priority' => 20, 'settings' => ['key_setting' => 'newsapi_key', 'implemented' => false]],
                ['key' => 'thesportsdb', 'name' => 'TheSportsDB', 'type' => 'sports', 'requires_key' => false, 'is_active' => false, 'priority' => 30, 'settings' => ['implemented' => false]],
                ['key' => 'api-sports', 'name' => 'API-SPORTS', 'type' => 'sports', 'requires_key' => true, 'is_active' => false, 'priority' => 40, 'settings' => ['implemented' => false]],
                ['key' => 'sportmonks', 'name' => 'SportMonks', 'type' => 'sports', 'requires_key' => true, 'is_active' => false, 'priority' => 50, 'settings' => ['implemented' => false]],
                ['key' => 'football-data', 'name' => 'football-data.org', 'type' => 'sports', 'requires_key' => true, 'is_active' => true, 'priority' => 60, 'settings' => ['key_setting' => 'football_data_api_token', 'implemented' => true]],
                ['key' => 'mysportsfeeds', 'name' => 'MySportsFeeds', 'type' => 'sports', 'requires_key' => true, 'is_active' => false, 'priority' => 70, 'settings' => ['implemented' => false]],
                ['key' => 'statorium', 'name' => 'Statorium Sports News API', 'type' => 'news', 'requires_key' => true, 'is_active' => false, 'priority' => 80, 'settings' => ['implemented' => false]],
                ['key' => 'rapidapi', 'name' => 'RapidAPI Sports/News', 'type' => 'news', 'requires_key' => true, 'is_active' => false, 'priority' => 90, 'settings' => ['implemented' => false]],
                ['key' => 'espn-hidden', 'name' => 'ESPN Hidden API', 'type' => 'sports', 'requires_key' => false, 'is_active' => false, 'is_unofficial' => true, 'priority' => 100, 'settings' => ['implemented' => false]],
            ];
            foreach ($sources as $source) {
                NewsSource::query()->updateOrCreate(['key' => $source['key']], $source);
            }
        }
    }

    public function down(): void
    {
        // Additive migration: no destructive rollback needed for production safety.
    }
};
