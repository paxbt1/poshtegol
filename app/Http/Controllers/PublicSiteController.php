<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\Team;
use App\Services\PublicPortal\PublicSiteData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PublicSiteController extends Controller
{

    public function cup()
    {
        return auth()->check() ? redirect()->route('dashboard') : app(\App\Services\UnauthorizedAccessService::class)->deny();
    }

    public function home(PublicSiteData $data)
    {
        return view('public.home', $data->home());
    }

    public function news(Request $request, PublicSiteData $data)
    {
        $query = trim((string) $request->query('q')) ?: null;
        $category = null;

        if ($request->query('category') && Schema::hasTable('news_categories')) {
            $category = NewsCategory::query()->where('slug', $request->query('category'))->first();
        }

        return view('public.news-index', $data->shell([
            'articles' => $data->newsIndex($query, $category?->id),
            'selectedCategory' => $category,
            'query' => $query,
        ]));
    }

    public function category(NewsCategory $category, Request $request, PublicSiteData $data)
    {
        abort_unless($category->is_active, 404);

        $query = trim((string) $request->query('q')) ?: null;

        return view('public.category', $data->shell([
            'category' => $category,
            'articles' => $data->newsIndex($query, $category->id),
            'query' => $query,
        ]));
    }

    public function show(NewsArticle $article, PublicSiteData $data)
    {
        abort_if($article->status !== 'published', 404);

        return view('public.news-show', $data->shell([
            'article' => $article->load('category'),
            'latestNews' => $data->articleSidebar($article->id),
            'relatedNews' => $data->relatedArticles($article),
        ]));
    }

    public function videos(PublicSiteData $data)
    {
        $videos = NewsArticle::query()
            ->where('status', 'published')
            ->where(function ($q) {
                $q->where('is_video', true)->orWhereNotNull('video_url');
            })
            ->orderByDesc('published_at')
            ->paginate(12);

        if ($videos->count() === 0) {
            $videos = NewsArticle::query()
                ->where('status', 'published')
                ->whereNotNull('slug')
                ->orderByDesc('published_at')
                ->paginate(12);
        }

        return view('public.videos', $data->shell(['articles' => $videos]));
    }

    public function liveScores(PublicSiteData $data)
    {
        return view('public.live-scores', $data->shell([
            'liveMatches' => $data->liveMatches(30),
            'todayMatches' => $data->todayMatches(30),
            'finishedMatches' => $data->finishedMatches(30),
            'upcomingMatches' => $data->upcomingMatches(30),
        ]));
    }

    public function fixtures(PublicSiteData $data)
    {
        return view('public.fixtures', $data->shell([
            'upcomingMatches' => $data->upcomingMatches(80),
            'finishedMatches' => $data->finishedMatches(20),
        ]));
    }

    public function teams(PublicSiteData $data)
    {
        $teams = Schema::hasTable('teams')
            ? Team::query()->orderBy('name_fa')->get()
            : collect();

        return view('public.teams', $data->shell(['teams' => $teams]));
    }

    public function search(Request $request, PublicSiteData $data)
    {
        $query = trim((string) $request->query('q')) ?: null;

        return view('public.search', $data->shell([
            'articles' => $data->newsIndex($query),
            'query' => $query,
        ]));
    }
}
