@php($newsAdmin = request()->routeIs('news-admin.*'))
@extends($newsAdmin ? 'layouts.news-admin' : 'layouts.admin')

@section('content')
<h1 class="title">Ù…Ø¯ÛŒØ±ÛŒØª Ø§Ø®Ø¨Ø§Ø± Ù¾Ø´Øª Ú¯Ù„</h1>
<p class="muted" style="line-height:1.9;margin-top:8px;">Ø§Ø®Ø¨Ø§Ø± Ù¾ÛŒØ´â€ŒÙØ±Ø¶ Ù…Ù†ØªØ´Ø± Ù…ÛŒâ€ŒØ´ÙˆÙ†Ø¯Ø› Ù…Ø¯ÛŒØ± Ù…ÛŒâ€ŒØªÙˆØ§Ù†Ø¯ Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒØŒ ØªÛŒØªØ± ÙØ§Ø±Ø³ÛŒØŒ Ø®Ù„Ø§ØµÙ‡ØŒ Ù…ØªÙ† Ù‚Ø§Ø¨Ù„ Ù†Ù…Ø§ÛŒØ´ØŒ ØªØµÙˆÛŒØ± Ù…Ø­Ù„ÛŒ Ùˆ ÙˆØ¶Ø¹ÛŒØª Ø§Ù†ØªØ´Ø§Ø± Ø±Ø§ ÙˆÛŒØ±Ø§ÛŒØ´ Ú©Ù†Ø¯.</p>

<div class="grid desktop-grid-3" style="margin-top:16px;">
    <x-stat-card label="Ú©Ù„ Ø®Ø¨Ø±Ù‡Ø§" :value="$articles->count()" hint="Ø¢Ø®Ø±ÛŒÙ† ÛµÛ° Ù…ÙˆØ±Ø¯" />
    <x-stat-card label="Ù…Ù†ØªØ´Ø±Ø´Ø¯Ù‡" :value="$articles->where('status', 'published')->count()" hint="Ù‚Ø§Ø¨Ù„ Ù†Ù…Ø§ÛŒØ´" />
    <x-stat-card label="ØªØ±Ø¬Ù…Ù‡â€ŒØ´Ø¯Ù‡" :value="$articles->whereNotNull('translated_title')->count()" hint="Ø¨Ø§ Ù…ØªØ±Ø¬Ù…" />
</div>

<x-ui.card style="margin-top:16px;">
    <div class="admin-user-row">
        <div>
            <h2 class="section-title" style="margin-top:0;">Ù‡Ù…Ú¯Ø§Ù…â€ŒØ³Ø§Ø²ÛŒ Ø¯Ø³ØªÛŒ</h2>
            <p class="muted">Ø¨Ø±Ø§ÛŒ Ø§Ø¬Ø±Ø§ÛŒ Ø®ÙˆØ¯Ú©Ø§Ø± Ù‡Ø± ÛŒÚ© Ø³Ø§Ø¹ØªØŒ Ú©Ø±ÙˆÙ† Laravel Scheduler Ø¨Ø§ÛŒØ¯ ÙØ¹Ø§Ù„ Ø¨Ø§Ø´Ø¯.</p>
        </div>
        <div class="admin-action-row">
            <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.sync' : 'admin.news.sync') }}">@csrf<button class="btn btn-primary" type="submit">Ø¯Ø±ÛŒØ§ÙØª Ø®Ø¨Ø±Ù‡Ø§ Ùˆ Ø¯Ø§Ù†Ù„ÙˆØ¯ ØªØµØ§ÙˆÛŒØ±</button></form>
            <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.test-gemini' : 'admin.news.test-gemini') }}">@csrf<button class="btn btn-outline" type="submit">ØªØ³Øª Gemini</button></form>
            <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.test-microsoft' : 'admin.news.test-microsoft') }}">@csrf<button class="btn btn-outline" type="submit">ØªØ³Øª Microsoft</button></form>
        </div>
    </div>
</x-ui.card>

<div class="grid" style="margin-top:16px;">
@forelse($articles as $article)
    <x-ui.card>
        <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.update' : 'admin.news.update', $article) }}" enctype="multipart/form-data" class="admin-news-edit">
            @csrf @method('PATCH')
            <div class="admin-news-thumb">
                @if($article->display_image_url)
                    <img src="{{ $article->display_image_url }}" alt="">
                @else
                    <span>Ø¨Ø¯ÙˆÙ† ØªØµÙˆÛŒØ±</span>
                @endif
            </div>
            <div class="admin-news-fields">
                <div class="grid desktop-grid-2">
                    <div class="field"><label>ØªÛŒØªØ± ÙØ§Ø±Ø³ÛŒ</label><input class="input" name="translated_title" value="{{ $article->translated_title ?: $article->original_title }}"></div>
                    <div class="field"><label>Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ</label><select class="input" name="category_id"><option value="">Ø¨Ø¯ÙˆÙ† Ø¯Ø³ØªÙ‡</option>@foreach(($categories ?? collect()) as $cat)<option value="{{ $cat->id }}" @selected($article->category_id === $cat->id)>{{ $cat->title }}</option>@endforeach</select></div>
                </div>
                <div class="field"><label>Ø®Ù„Ø§ØµÙ‡ ÙØ§Ø±Ø³ÛŒ</label><textarea class="input" name="translated_summary" style="height:82px;padding-top:10px;">{{ $article->translated_summary ?: $article->original_description }}</textarea></div>
                <div class="field"><label>Ù…ØªÙ† Ù‚Ø§Ø¨Ù„ Ù†Ù…Ø§ÛŒØ´ Ø¯Ø± ØµÙØ­Ù‡ Ø®Ø¨Ø±</label><textarea class="input" name="translated_body" style="height:130px;padding-top:10px;">{{ $article->translated_body ?: $article->original_content }}</textarea></div>
                <div class="grid desktop-grid-3">
                    <div class="field"><label>ÙˆØ¶Ø¹ÛŒØª</label><select class="input" name="status"><option value="published" @selected($article->status === 'published')>Ù…Ù†ØªØ´Ø± Ø´Ø¯Ù‡</option><option value="draft" @selected($article->status === 'draft')>Ù¾ÛŒØ´â€ŒÙ†ÙˆÛŒØ³</option><option value="hidden" @selected($article->status === 'hidden')>Ù…Ø®ÙÛŒ</option></select></div>
                    <div class="field"><label>ØªØµÙˆÛŒØ± Ù…Ø­Ù„ÛŒ Ø¬Ø¯ÛŒØ¯</label><input class="input" type="file" name="local_image_file" accept="image/*"></div>
                    <div class="field"><label>Ù„ÛŒÙ†Ú© Ù…Ù†Ø¨Ø¹</label><input class="input" dir="ltr" name="original_url" value="{{ $article->original_url }}"></div>
                </div>
                <label class="toggle-row compact"><input type="checkbox" name="is_featured" value="1" @checked($article->is_featured)> <span>Ø®Ø¨Ø± ÙˆÛŒÚ˜Ù‡</span></label>
                <div class="muted small" style="line-height:1.8;">Ù…Ù†Ø¨Ø¹: {{ $article->safe_source_name }} Â· ØªØ§Ø±ÛŒØ®: {{ \App\Support\Jalali::format($article->published_at, 'Y/m/d H:i') }} Â· ØªÛŒØªØ± Ø§ØµÙ„ÛŒ: {{ $article->original_title }}</div>
                <div class="admin-action-row"><button class="btn btn-primary" type="submit">Ø°Ø®ÛŒØ±Ù‡ Ø®Ø¨Ø±</button><a class="btn btn-outline" target="_blank" href="{{ \Illuminate\Support\Facades\Route::has('public.news.show') ? route('public.news.show', $article) : '#' }}">Ù†Ù…Ø§ÛŒØ´</a></div>
            </div>
        </form>
        <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.destroy' : 'admin.news.destroy', $article) }}" onsubmit="return confirm('Ø§ÛŒÙ† Ø®Ø¨Ø± Ø­Ø°Ù Ø´ÙˆØ¯ØŸ')" style="margin-top:10px;">
            @csrf @method('DELETE')
            <button class="btn btn-soft" type="submit">Ø­Ø°Ù Ø®Ø¨Ø±</button>
        </form>
    </x-ui.card>
@empty
    <x-ui.card><p class="muted">Ù‡Ù†ÙˆØ² Ø®Ø¨Ø±ÛŒ Ø¯Ø±ÛŒØ§ÙØª Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª.</p></x-ui.card>
@endforelse
</div>

<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">Ø¢Ø®Ø±ÛŒÙ† Ù„Ø§Ú¯â€ŒÙ‡Ø§</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>ÙˆØ¶Ø¹ÛŒØª</th><th>Ø¯Ø±ÛŒØ§ÙØªÛŒ</th><th>Ø§ÛŒØ¬Ø§Ø¯</th><th>Ø¨Ù‡â€ŒØ±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ</th><th>ØªØ±Ø¬Ù…Ù‡</th><th>Ù¾ÛŒØ§Ù…</th><th>Ø²Ù…Ø§Ù†</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr><td>{{ $log->status }}</td><td>{{ $log->items_received }}</td><td>{{ $log->items_created }}</td><td>{{ $log->items_updated }}</td><td>{{ $log->items_translated }}</td><td>{{ $log->message }}</td><td>{{ \App\Support\Jalali::format($log->created_at, 'Y/m/d H:i') }}</td></tr>
                @empty
                    <tr><td colspan="7" class="muted">Ù„Ø§Ú¯ÛŒ Ø«Ø¨Øª Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
