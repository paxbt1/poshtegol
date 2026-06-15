@extends('layouts.public')

@section('content')
<x-public.ad-slot name="article_top" :slots="$adSlots" />

<article class="pg-article-layout">
    <main class="pg-article-main">
        <div class="pg-article-card">
            <div class="pg-breadcrumb"><a href="{{ route('public.home') }}">پشت گل</a><span>/</span><a href="{{ route('public.news') }}">اخبار</a>@if($article->category)<span>/</span><a href="{{ route('public.category', $article->category) }}">{{ $article->category->title }}</a>@endif</div>
            <h1>{{ $article->display_title }}</h1>
            <div class="pg-article-meta">
                <span>{{ $article->safe_source_name }}</span>
                @if($article->published_at)<span>{{ \App\Support\Jalali::format($article->published_at, 'Y/m/d H:i') }}</span>@endif
                <span>{{ $article->translation_status === 'translated' ? 'ترجمه‌شده' : 'ترجمه خودکار/در انتظار' }}</span>
            </div>
            @if($article->display_image_url)
                <figure class="pg-article-image"><img src="{{ $article->display_image_url }}" alt="{{ $article->display_title }}"></figure>
            @endif
            <div class="pg-article-summary">{{ $article->display_summary }}</div>
            <x-public.ad-slot name="article_middle" :slots="$adSlots" />
            <div class="pg-article-body">
                {!! nl2br(e($article->display_body ?: $article->display_summary)) !!}
            </div>
            <div class="pg-source-box">
                <strong>منبع اصلی خبر</strong>
                <p>این خبر از منبع خارجی دریافت و برای کاربران فارسی‌زبان خلاصه/ترجمه شده است. برای مطالعه کامل و مشاهده جزئیات رسمی، به منبع اصلی مراجعه کنید.</p>
                @if($article->original_url)
                    <a class="pg-primary-btn" href="{{ $article->original_url }}" target="_blank" rel="nofollow sponsored noopener">ادامه خبر در منبع اصلی</a>
                @endif
            </div>
        </div>

        <x-public.ad-slot name="article_bottom" :slots="$adSlots" />
        <div class="pg-article-ad-grid">
            @foreach(range(1, 10) as $i)
                <x-public.ad-slot :name="'article_after_'.$i" :slots="$adSlots" />
            @endforeach
        </div>

        <div class="pg-section-head"><h2>خبرهای مرتبط</h2></div>
        <div class="pg-news-grid pg-news-grid-small">
            @foreach($relatedNews as $related)
                <x-public.news-card :article="$related" />
            @endforeach
        </div>
    </main>

    <aside class="pg-sidebar pg-article-side">
        <x-public.ad-slot name="sidebar_top" :slots="$adSlots" />
        <div class="pg-side-card sticky">
            <div class="pg-section-head compact"><strong>خبرهای دیگر</strong></div>
            @foreach($latestNews as $item)
                <x-public.news-card :article="$item" variant="compact" />
            @endforeach
        </div>
    </aside>
</article>
@endsection
