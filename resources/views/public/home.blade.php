@extends('layouts.public')

@section('content')
<x-public.ad-slot name="home_top_billboard" :slots="$adSlots" />

<section class="pg-hero-grid">
    @php($hero = $featuredArticles->first())
    <div class="pg-hero-panel">
        @if($hero)
            <x-public.news-card :article="$hero" variant="hero" />
        @else
            <div class="pg-empty-hero">
                <span>پشت گل</span>
                <h1>رسانه فارسی فوتبال برای جام جهانی ۲۰۲۶</h1>
                <p>بعد از تنظیم کلید سرویس‌های خبری از پنل مدیریت، آخرین تیترها، خلاصه فارسی و تصاویر محلی در همین بخش نمایش داده می‌شود.</p>
            </div>
        @endif
    </div>
    <aside class="pg-hero-side">
        <div class="pg-score-strip-card">
            <div class="pg-section-head compact"><strong>اسکوربورد زنده</strong><a href="{{ route('public.live-scores') }}">همه نتایج</a></div>
            <div class="pg-mini-score-list">
                @forelse(($liveMatches->isNotEmpty() ? $liveMatches : $upcomingMatches)->take(4) as $match)
                    <x-public.match-row :match="$match" />
                @empty
                    <p class="pg-muted">هنوز بازی‌ای برای نمایش وجود ندارد. از مدیریت، همگام‌سازی football-data را اجرا کنید.</p>
                @endforelse
            </div>
        </div>
        <x-public.ad-slot name="home_hero_inside" :slots="$adSlots" />
    </aside>
</section>

<section class="pg-home-layout">
    <main>
        <div class="pg-section-head"><h2>آخرین خبرها</h2><a href="{{ route('public.news') }}">مشاهده همه</a></div>
        <div class="pg-news-grid">
            @foreach($latestArticles->take(6) as $article)
                <x-public.news-card :article="$article" />
            @endforeach
        </div>

        <x-public.ad-slot name="home_between_news" :slots="$adSlots" />

        <div class="pg-section-head"><h2>ویژه جام جهانی ۲۰۲۶</h2><a href="{{ route('public.category', 'world-cup-2026') }}">آرشیو جام جهانی</a></div>
        <div class="pg-horizontal-list">
            @forelse(($worldCupArticles->isNotEmpty() ? $worldCupArticles : $latestArticles->skip(6))->take(6) as $article)
                <x-public.news-card :article="$article" variant="compact" />
            @empty
                <div class="pg-soft-card">هنوز خبر ویژه جام جهانی ثبت نشده است.</div>
            @endforelse
        </div>

        <div class="pg-section-head"><h2>تحلیل و آمار</h2><a href="{{ route('public.category', 'analysis') }}">بیشتر</a></div>
        <div class="pg-news-grid pg-news-grid-small">
            @forelse(($analysisArticles->isNotEmpty() ? $analysisArticles : $latestArticles->skip(2))->take(4) as $article)
                <x-public.news-card :article="$article" />
            @empty
                <div class="pg-soft-card">تحلیل‌ها بعد از همگام‌سازی اخبار نمایش داده می‌شوند.</div>
            @endforelse
        </div>
    </main>

    <aside class="pg-sidebar">
        <x-public.ad-slot name="home_sidebar_top" :slots="$adSlots" />
        <div class="pg-side-card">
            <div class="pg-section-head compact"><strong>ترند امروز</strong></div>
            @foreach($latestArticles->take(7) as $article)
                <x-public.news-card :article="$article" variant="compact" />
            @endforeach
        </div>
        <div class="pg-side-card">
            <div class="pg-section-head compact"><strong>دسته‌بندی‌ها</strong></div>
            <div class="pg-chip-list">
                @foreach($categories as $category)
                    <a href="{{ route('public.category', $category) }}">{{ $category->title }}</a>
                @endforeach
            </div>
        </div>
    </aside>
</section>
@endsection
