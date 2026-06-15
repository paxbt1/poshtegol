@props(['article', 'variant' => 'default'])

@php
    $title = $article->display_title
        ?? $article->translated_title
        ?? $article->original_title
        ?? 'خبر بدون عنوان';

    $summary = $article->display_summary
        ?? $article->translated_summary
        ?? $article->original_description
        ?? '';

    $imageUrl = $article->display_image_url
        ?? ($article->local_image_path ? asset($article->local_image_path) : null);

    $newsUrl = ! empty($article->slug)
        ? url('/news/'.$article->slug)
        : url('/news');
@endphp

<a class="pg-news-card pg-news-card-{{ $variant }}" href="{{ $newsUrl }}">
    <span class="pg-news-image">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $title }}" loading="lazy">
        @else
            <span class="pg-image-fallback">پشت گل</span>
        @endif
    </span>

    <span class="pg-news-text">
        <span class="pg-news-meta">
            {{ $article->category?->title ?? 'فوتبال' }}
            @if($article->published_at)
                · {{ \App\Support\Jalali::format($article->published_at, 'Y/m/d H:i') }}
            @endif
        </span>

        <strong>{{ $title }}</strong>

        @if($variant !== 'compact' && filled($summary))
            <em>{{ \Illuminate\Support\Str::limit(strip_tags($summary), $variant === 'hero' ? 130 : 95) }}</em>
        @endif

        <small>{{ $article->safe_source_name ?? $article->source_name ?? 'منبع خارجی' }}</small>
    </span>
</a>
