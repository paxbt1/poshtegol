@extends('layouts.app')

@section('content')
<div class="news-detail-page">
    <main class="news-detail-main">
        <article class="card news-detail-card-pro">
            <div class="news-detail-meta">
                <span class="brand-pill">خبر جام جهانی</span>
                <span class="muted small">{{ $article->safe_source_name }} @if($article->published_at) · {{ \App\Support\Jalali::format($article->published_at, 'Y/m/d H:i') }} @endif</span>
            </div>

            <h1 class="news-detail-title">{{ $article->display_title }}</h1>

            @if($article->display_image_url)
                <figure class="news-hero-figure">
                    <img class="news-detail-image" src="{{ $article->display_image_url }}" alt="{{ $article->display_title }}" loading="lazy">
                </figure>
            @endif

            @if($article->display_summary)
                <section class="news-summary-box">
                    <strong>خلاصه سریع</strong>
                    <p>{{ $article->display_summary }}</p>
                </section>
            @endif

            <section class="news-body-box">
                <h2>متن خبر</h2>
                @if($article->display_body)
                    @foreach(preg_split('/\R{2,}|\n/u', $article->display_body) as $paragraph)
                        @if(trim($paragraph) !== '')
                            <p>{{ trim($paragraph) }}</p>
                        @endif
                    @endforeach
                @else
                    <p class="muted">متن بیشتری از طرف وب‌سرویس برای این خبر ارسال نشده است.</p>
                @endif
            </section>

            <div class="news-source-note">
                متن بالا فقط بر اساس محتوایی است که وب‌سرویس خبر در اختیار اپ قرار داده است. برای مطالعه نسخه کامل و رسمی، منبع اصلی را باز کنید.
            </div>

            <div class="news-detail-actions">
                <a class="btn btn-primary" href="{{ $article->original_url }}" target="_blank" rel="noopener noreferrer nofollow">ادامه خبر در منبع اصلی</a>
                <a class="btn btn-soft" href="{{ route('dashboard') }}">بازگشت به داشبورد</a>
            </div>
        </article>
    </main>

    <aside class="news-detail-aside">
        <x-ui.card class="news-side-card">
            <h2 class="section-title" style="margin-top:0;">خبرهای دیگر</h2>
            <div class="news-list compact-news-list">
                @forelse($latestNews as $item)
                    <a class="news-card" href="{{ route('public.news.show', $item->slug) }}">
                        <span class="news-thumb">
                            @if($item->display_image_url)
                                <img src="{{ $item->display_image_url }}" alt="{{ $item->display_title }}" loading="lazy">
                            @else
                                <span>خبر</span>
                            @endif
                        </span>
                        <span class="news-content">
                            <strong>{{ $item->display_title }}</strong>
                            <span class="news-meta">{{ $item->safe_source_name }}</span>
                        </span>
                    </a>
                @empty
                    <p class="muted">خبر دیگری برای نمایش وجود ندارد.</p>
                @endforelse
            </div>
        </x-ui.card>
    </aside>
</div>
@endsection
