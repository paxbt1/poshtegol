<?php

namespace App\Services\PublicPortal;

use App\Models\AdSlot;
use App\Models\FootballMatch;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\AppSetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PublicSiteData
{
    public function shell(array $extra = []): array
    {
        return $extra + [
            'siteName' => AppSetting::getValue('public_site_name', 'پشت گل'),
            'siteTagline' => AppSetting::getValue('public_site_tagline', 'خبر، نتیجه زنده و روایت فارسی فوتبال جهان'),
            'categories' => $this->categories(),
            'adSlots' => $this->adSlots(),
        ];
    }

    public function home(): array
    {
        $featured = $this->publishedArticles()
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        if ($featured->isEmpty()) {
            $featured = $this->publishedArticles()->orderByDesc('published_at')->take(5)->get();
        }

        $latest = $this->publishedArticles()
            ->with('category')
            ->orderByDesc('published_at')
            ->latest()
            ->take(14)
            ->get();

        $worldCup = $this->articlesByCategory('world-cup-2026', 6);
        $analysis = $this->articlesByCategory('analysis', 5);
        $videos = $this->publishedArticles()->where('is_video', true)->orderByDesc('published_at')->take(6)->get();
        if ($videos->isEmpty()) {
            $videos = $this->articlesByCategory('videos', 6);
        }

        return $this->shell([
            'featuredArticles' => $featured,
            'latestArticles' => $latest,
            'worldCupArticles' => $worldCup,
            'analysisArticles' => $analysis,
            'videoArticles' => $videos,
            'todayMatches' => $this->todayMatches(8),
            'upcomingMatches' => $this->upcomingMatches(6),
            'liveMatches' => $this->liveMatches(6),
        ]);
    }

    public function newsIndex(?string $query = null, ?int $categoryId = null): LengthAwarePaginator
    {
        $builder = $this->publishedArticles()->with('category');

        if ($query) {
            $builder->where(function ($q) use ($query) {
                $q->where('translated_title', 'like', "%{$query}%")
                    ->orWhere('original_title', 'like', "%{$query}%")
                    ->orWhere('translated_summary', 'like', "%{$query}%")
                    ->orWhere('original_description', 'like', "%{$query}%");
            });
        }

        if ($categoryId) {
            $builder->where('category_id', $categoryId);
        }

        return $builder->orderByDesc('published_at')->latest()->paginate(12)->withQueryString();
    }

    public function categories(): EloquentCollection|Collection
    {
        if (! Schema::hasTable('news_categories')) {
            return collect();
        }

        return NewsCategory::query()->where('is_active', true)->orderBy('sort_order')->get();
    }

    public function adSlots(): Collection
    {
        if (! Schema::hasTable('ad_slots')) {
            return collect();
        }

        return AdSlot::query()->with('activeAds')->where('is_active', true)->orderBy('sort_order')->get()->keyBy('key');
    }

    public function articleSidebar(?int $excludeId = null): EloquentCollection|Collection
    {
        return $this->publishedArticles()
            ->when($excludeId, fn ($q) => $q->whereKeyNot($excludeId))
            ->orderByDesc('published_at')
            ->take(8)
            ->get();
    }

    public function relatedArticles(NewsArticle $article): EloquentCollection|Collection
    {
        return $this->publishedArticles()
            ->whereKeyNot($article->id)
            ->when($article->category_id, fn ($q) => $q->where('category_id', $article->category_id))
            ->orderByDesc('published_at')
            ->take(6)
            ->get();
    }

    public function liveMatches(int $limit = 20): EloquentCollection|Collection
    {
        if (! Schema::hasTable('matches')) {
            return collect();
        }

        return FootballMatch::query()
            ->with(['homeTeam', 'awayTeam'])
            ->whereIn('status', ['live_first_half', 'halftime', 'live_second_half'])
            ->orderBy('starts_at')
            ->take($limit)
            ->get();
    }

    public function todayMatches(int $limit = 20): EloquentCollection|Collection
    {
        if (! Schema::hasTable('matches')) {
            return collect();
        }

        return FootballMatch::query()
            ->with(['homeTeam', 'awayTeam'])
            ->whereDate('starts_at', today())
            ->orderBy('starts_at')
            ->take($limit)
            ->get();
    }

    public function upcomingMatches(int $limit = 20): EloquentCollection|Collection
    {
        if (! Schema::hasTable('matches')) {
            return collect();
        }

        return FootballMatch::query()
            ->with(['homeTeam', 'awayTeam'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->take($limit)
            ->get();
    }

    public function finishedMatches(int $limit = 20): EloquentCollection|Collection
    {
        if (! Schema::hasTable('matches')) {
            return collect();
        }

        return FootballMatch::query()
            ->with(['homeTeam', 'awayTeam'])
            ->whereIn('status', ['finished', 'awarded', 'after_extra_time', 'after_penalties'])
            ->orderByDesc('starts_at')
            ->take($limit)
            ->get();
    }

    public function articlesByCategory(string $slug, int $limit): EloquentCollection|Collection
    {
        if (! Schema::hasTable('news_categories')) {
            return collect();
        }

        $category = NewsCategory::query()->where('slug', $slug)->first();
        if (! $category) {
            return collect();
        }

        return $this->publishedArticles()
            ->where('category_id', $category->id)
            ->orderByDesc('published_at')
            ->take($limit)
            ->get();
    }

    private function publishedArticles()
    {
        return NewsArticle::query()
            ->where('status', 'published')
            ->whereNotNull('slug');
    }
}
