<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('news_categories')) {
            Schema::create('news_categories', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('news_sources')) {
            Schema::create('news_sources', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('name');
                $table->string('type')->default('news')->index();
                $table->boolean('is_active')->default(false)->index();
                $table->boolean('is_official')->default(true);
                $table->boolean('is_unofficial')->default(false);
                $table->boolean('requires_key')->default(true);
                $table->json('settings')->nullable();
                $table->unsignedInteger('priority')->default(100)->index();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ad_slots')) {
            Schema::create('ad_slots', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('title');
                $table->string('placement')->index();
                $table->string('device')->default('all')->index();
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ads')) {
            Schema::create('ads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ad_slot_id')->constrained('ad_slots')->cascadeOnDelete();
                $table->string('title');
                $table->text('image_desktop')->nullable();
                $table->text('image_mobile')->nullable();
                $table->text('link_url')->nullable();
                $table->boolean('target_blank')->default(true);
                $table->boolean('rel_nofollow')->default(true);
                $table->boolean('rel_sponsored')->default(true);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('impressions_count')->default(0);
                $table->unsignedInteger('clicks_count')->default(0);
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sports_scoreboards')) {
            Schema::create('sports_scoreboards', function (Blueprint $table) {
                $table->id();
                $table->string('provider')->index();
                $table->string('external_id')->nullable()->index();
                $table->string('competition')->nullable()->index();
                $table->string('season')->nullable()->index();
                $table->string('home_team_name')->nullable();
                $table->string('away_team_name')->nullable();
                $table->text('home_team_logo')->nullable();
                $table->text('away_team_logo')->nullable();
                $table->text('local_home_team_logo')->nullable();
                $table->text('local_away_team_logo')->nullable();
                $table->timestamp('starts_at')->nullable()->index();
                $table->string('status')->default('scheduled')->index();
                $table->integer('minute')->nullable();
                $table->integer('home_score')->nullable();
                $table->integer('away_score')->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->string('hash', 64)->unique();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('news_articles')) {
            Schema::table('news_articles', function (Blueprint $table) {
                if (! Schema::hasColumn('news_articles', 'category_id')) {
                    $table->foreignId('category_id')->nullable()->after('hash')->constrained('news_categories')->nullOnDelete();
                }
                if (! Schema::hasColumn('news_articles', 'duplicate_of_article_id')) {
                    $table->foreignId('duplicate_of_article_id')->nullable()->after('category_id')->constrained('news_articles')->nullOnDelete();
                }
                if (! Schema::hasColumn('news_articles', 'translation_status')) {
                    $table->string('translation_status')->default('pending')->after('duplicate_of_article_id')->index();
                }
                if (! Schema::hasColumn('news_articles', 'is_video')) {
                    $table->boolean('is_video')->default(false)->after('translation_status')->index();
                }
                if (! Schema::hasColumn('news_articles', 'video_url')) {
                    $table->text('video_url')->nullable()->after('is_video');
                }
            });
        }

        $this->seedDefaults();
    }

    public function down(): void
    {
        Schema::dropIfExists('sports_scoreboards');
        Schema::dropIfExists('ads');
        Schema::dropIfExists('ad_slots');
        Schema::dropIfExists('news_sources');
        Schema::dropIfExists('news_categories');
    }

    private function seedDefaults(): void
    {
        $now = now();

        $categories = [
            ['title' => 'جام جهانی ۲۰۲۶', 'slug' => 'world-cup-2026', 'description' => 'آخرین خبرهای جام جهانی ۲۰۲۶، برنامه بازی‌ها و حواشی تیم‌ها', 'sort_order' => 1],
            ['title' => 'فوتبال جهان', 'slug' => 'world-football', 'description' => 'خبرهای مهم فوتبال اروپا، آمریکا و تیم‌های ملی', 'sort_order' => 2],
            ['title' => 'ایران و آسیا', 'slug' => 'iran-asia', 'description' => 'اخبار تیم ملی ایران، رقبای آسیایی و انتخابی‌ها', 'sort_order' => 3],
            ['title' => 'نقل‌وانتقالات', 'slug' => 'transfers', 'description' => 'شایعه‌ها و خبرهای نقل‌وانتقالات فوتبال', 'sort_order' => 4],
            ['title' => 'تحلیل و آمار', 'slug' => 'analysis', 'description' => 'آمار، تحلیل بازی‌ها و روایت‌های تاکتیکی', 'sort_order' => 5],
            ['title' => 'ویدئو', 'slug' => 'videos', 'description' => 'ویدئوهای فوتبالی، خلاصه و لحظه‌های ویژه', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            DB::table('news_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                $category + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $sources = [
            ['key' => 'gnews', 'name' => 'GNews', 'type' => 'news', 'is_active' => true, 'is_official' => true, 'is_unofficial' => false, 'requires_key' => true, 'priority' => 10],
            ['key' => 'newsapi', 'name' => 'NewsAPI.org', 'type' => 'news', 'is_active' => false, 'is_official' => true, 'is_unofficial' => false, 'requires_key' => true, 'priority' => 20],
            ['key' => 'guardian', 'name' => 'The Guardian Open Platform', 'type' => 'news', 'is_active' => false, 'is_official' => true, 'is_unofficial' => false, 'requires_key' => true, 'priority' => 30],
            ['key' => 'thesportsdb', 'name' => 'TheSportsDB', 'type' => 'media', 'is_active' => false, 'is_official' => true, 'is_unofficial' => false, 'requires_key' => false, 'priority' => 40],
            ['key' => 'api_sports', 'name' => 'API-Sports / API-Football', 'type' => 'sports', 'is_active' => false, 'is_official' => true, 'is_unofficial' => false, 'requires_key' => true, 'priority' => 50],
            ['key' => 'sportmonks', 'name' => 'Sportmonks', 'type' => 'sports', 'is_active' => false, 'is_official' => true, 'is_unofficial' => false, 'requires_key' => true, 'priority' => 60],
            ['key' => 'football_data', 'name' => 'football-data.org', 'type' => 'sports', 'is_active' => true, 'is_official' => true, 'is_unofficial' => false, 'requires_key' => true, 'priority' => 70],
            ['key' => 'mysportsfeeds', 'name' => 'MySportsFeeds', 'type' => 'sports', 'is_active' => false, 'is_official' => true, 'is_unofficial' => false, 'requires_key' => true, 'priority' => 80],
            ['key' => 'statorium', 'name' => 'Statorium Sports News API', 'type' => 'news', 'is_active' => false, 'is_official' => true, 'is_unofficial' => false, 'requires_key' => true, 'priority' => 90],
            ['key' => 'rapidapi', 'name' => 'RapidAPI Football News', 'type' => 'news', 'is_active' => false, 'is_official' => true, 'is_unofficial' => false, 'requires_key' => true, 'priority' => 100],
            ['key' => 'espn_hidden', 'name' => 'ESPN Hidden API', 'type' => 'news', 'is_active' => false, 'is_official' => false, 'is_unofficial' => true, 'requires_key' => false, 'priority' => 999],
        ];

        foreach ($sources as $source) {
            DB::table('news_sources')->updateOrInsert(
                ['key' => $source['key']],
                $source + ['settings' => json_encode([], JSON_UNESCAPED_UNICODE), 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $slots = [
            ['key' => 'home_top_billboard', 'title' => 'بنر بزرگ بالای صفحه اصلی', 'placement' => 'home_top', 'width' => 1200, 'height' => 180, 'sort_order' => 1],
            ['key' => 'home_hero_inside', 'title' => 'تبلیغ داخل هیرو صفحه اصلی', 'placement' => 'home_hero', 'width' => 728, 'height' => 90, 'sort_order' => 2],
            ['key' => 'home_sidebar_top', 'title' => 'ستون کناری صفحه اصلی بالا', 'placement' => 'home_sidebar_top', 'width' => 336, 'height' => 280, 'sort_order' => 3],
            ['key' => 'home_between_news', 'title' => 'بین خبرهای صفحه اصلی', 'placement' => 'home_between_news', 'width' => 970, 'height' => 90, 'sort_order' => 4],
            ['key' => 'category_top', 'title' => 'بالای صفحه دسته‌بندی', 'placement' => 'category_top', 'width' => 970, 'height' => 90, 'sort_order' => 5],
            ['key' => 'article_top', 'title' => 'بالای صفحه خبر', 'placement' => 'article_top', 'width' => 970, 'height' => 90, 'sort_order' => 6],
            ['key' => 'article_middle', 'title' => 'میان متن خبر', 'placement' => 'article_middle', 'width' => 728, 'height' => 90, 'sort_order' => 7],
            ['key' => 'article_bottom', 'title' => 'پایین صفحه خبر', 'placement' => 'article_bottom', 'width' => 970, 'height' => 90, 'sort_order' => 8],
            ['key' => 'scores_sidebar', 'title' => 'کنار نتایج زنده', 'placement' => 'scores_sidebar', 'width' => 336, 'height' => 280, 'sort_order' => 9],
            ['key' => 'videos_top', 'title' => 'بالای صفحه ویدئو', 'placement' => 'videos_top', 'width' => 970, 'height' => 90, 'sort_order' => 10],
            ['key' => 'mobile_sticky_bottom', 'title' => 'بنر چسبان موبایل پایین صفحه', 'placement' => 'mobile_sticky_bottom', 'device' => 'mobile', 'width' => 360, 'height' => 60, 'sort_order' => 11],
            ['key' => 'header_under_nav', 'title' => 'زیر منوی اصلی', 'placement' => 'header_under_nav', 'width' => 1200, 'height' => 120, 'sort_order' => 12],
        ];

        foreach ($slots as $slot) {
            DB::table('ad_slots')->updateOrInsert(
                ['key' => $slot['key']],
                array_merge(['device' => 'all', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now], $slot)
            );
        }

        $settings = [
            'app_title' => 'پشت گل',
            'public_site_name' => 'پشت گل',
            'public_site_tagline' => 'خبر، نتیجه زنده و روایت فارسی فوتبال جهان',
            'public_site_domain' => 'poshtegol.ir',
            'unauthorized_access_mode' => 'show_public_homepage',
            'unauthorized_redirect_url' => 'https://poshtegol.ir',
            'news_query' => 'FIFA World Cup 2026 OR football OR soccer',
            'news_enabled' => '1',
            'news_provider' => 'gnews',
            'news_download_images' => '1',
            'public_ads_enabled' => '1',
        ];

        foreach ($settings as $key => $value) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'is_private' => false, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
};
