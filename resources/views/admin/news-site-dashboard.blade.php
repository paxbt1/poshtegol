@extends('layouts.news-admin')

@section('content')
<h1 class="title">Ø¯Ø§Ø´Ø¨ÙˆØ±Ø¯ Ù…Ø¯ÛŒØ±ÛŒØª Ø®Ø¨Ø±ÛŒ Ù¾Ø´Øª Ú¯Ù„</h1>
<p class="muted" style="line-height:1.9;margin-top:8px;">Ø§ÛŒÙ† Ù¾Ù†Ù„ ÙÙ‚Ø· Ø¨Ø±Ø§ÛŒ Ø³Ø§ÛŒØª Ø®Ø¨Ø±ÛŒ Ù¾Ø´Øª Ú¯Ù„ Ø§Ø³Øª Ùˆ Ø§Ø² Ù¾Ù†Ù„ Ú©Ø§Ù¾ Ø®Ø§Ù†ÙˆØ§Ø¯Ú¯ÛŒ Ø¬Ø¯Ø§ Ø´Ø¯Ù‡ Ø§Ø³Øª.</p>

<div class="grid desktop-grid-3" style="margin-top:16px;">
    <x-stat-card label="Ú©Ù„ Ø®Ø¨Ø±Ù‡Ø§" :value="$articlesCount" hint="Ù‡Ù…Ù‡ Ø®Ø¨Ø±Ù‡Ø§ÛŒ Ø¯Ø±ÛŒØ§ÙØªâ€ŒØ´Ø¯Ù‡" />
    <x-stat-card label="Ù…Ù†ØªØ´Ø±Ø´Ø¯Ù‡" :value="$publishedCount" hint="Ù†Ù…Ø§ÛŒØ´ Ø¯Ø± Ø³Ø§ÛŒØª" />
    <x-stat-card label="Ø¯Ø³ØªÙ‡ ÙØ¹Ø§Ù„" :value="$categoriesCount" hint="Ù…Ù†ÙˆÛŒ Ø³Ø§ÛŒØª" />
    <x-stat-card label="ØªØ¨Ù„ÛŒØº ÙØ¹Ø§Ù„" :value="$activeAdsCount" hint="Ø¨Ù†Ø±Ù‡Ø§ÛŒ Ù†Ù…Ø§ÛŒØ´ÛŒ" />
    <x-stat-card label="Ù…Ù†Ø¨Ø¹ ÙØ¹Ø§Ù„" :value="$activeSourcesCount" hint="Ø®Ø¨Ø±/Ø¯Ø§Ø¯Ù‡ ÙÙˆØªØ¨Ø§Ù„" />
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">Ø¯Ø³ØªØ±Ø³ÛŒ Ø³Ø±ÛŒØ¹</h2>
        <div class="admin-action-row">
            <a class="btn btn-primary" href="{{ route('news-admin.news.index') }}">Ù…Ø¯ÛŒØ±ÛŒØª Ø§Ø®Ø¨Ø§Ø±</a>
            <a class="btn btn-outline" href="{{ route('news-admin.settings') }}">ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø³Ø±ÙˆÛŒØ³â€ŒÙ‡Ø§</a>
        </div>
    </x-ui.card>
</div>

<div class="grid desktop-grid-2" style="margin-top:16px;">
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">Ø¢Ø®Ø±ÛŒÙ† Ø®Ø¨Ø±Ù‡Ø§</h2>
        <div class="admin-list-mini">
            @forelse($latestArticles as $article)
                <a href="{{ \Illuminate\Support\Facades\Route::has('public.news.show') ? route('public.news.show', $article) : '#' }}" target="_blank">
                    <strong>{{ $article->display_title }}</strong>
                    <span>{{ $article->category?->title ?: 'Ø¨Ø¯ÙˆÙ† Ø¯Ø³ØªÙ‡' }} Â· {{ $article->status }}</span>
                </a>
            @empty
                <p class="muted">Ù‡Ù†ÙˆØ² Ø®Ø¨Ø±ÛŒ Ø¯Ø±ÛŒØ§ÙØª Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª.</p>
            @endforelse
        </div>
    </x-ui.card>

    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">Ø¢Ø®Ø±ÛŒÙ† Ù‡Ù…Ú¯Ø§Ù…â€ŒØ³Ø§Ø²ÛŒâ€ŒÙ‡Ø§</h2>
        <div class="admin-list-mini">
            @forelse($latestLogs as $log)
                <div class="mini-log {{ $log->status }}">
                    <strong>{{ $log->status === 'success' ? 'Ù…ÙˆÙÙ‚' : 'Ù†Ø§Ù…ÙˆÙÙ‚' }} Â· {{ $log->provider }}</strong>
                    <span>{{ $log->message }} Â· {{ \App\Support\Jalali::format($log->created_at, 'Y/m/d H:i') }}</span>
                </div>
            @empty
                <p class="muted">Ù„Ø§Ú¯ÛŒ Ø«Ø¨Øª Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª.</p>
            @endforelse
        </div>
    </x-ui.card>
</div>
@endsection
