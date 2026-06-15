<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Support\Facades\Schema;

class NewsController extends Controller
{
    public function show(NewsArticle $article)
    {
        abort_if($article->status !== 'published', 404);

        $latestNews = Schema::hasTable('news_articles')
            ? NewsArticle::query()
                ->where('status', 'published')
                ->whereNotNull('slug')
                ->whereKeyNot($article->id)
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at')
                ->latest()
                ->take(8)
                ->get()
            : collect();

        return view('news.show', [
            'article' => $article,
            'latestNews' => $latestNews,
        ]);
    }
}
