@php($newsAdmin = request()->routeIs('news-admin.*'))
@extends($newsAdmin ? 'layouts.news-admin' : 'layouts.admin')

@section('content')
<h1 class="title">Ã™â€¦Ã˜Â¯Ã›Å’Ã˜Â±Ã›Å’Ã˜Âª Ã˜Â§Ã˜Â®Ã˜Â¨Ã˜Â§Ã˜Â± Ã™Â¾Ã˜Â´Ã˜Âª ÃšÂ¯Ã™â€ž</h1>
<p class="muted" style="line-height:1.9;margin-top:8px;">Ã˜Â§Ã˜Â®Ã˜Â¨Ã˜Â§Ã˜Â± Ã™Â¾Ã›Å’Ã˜Â´Ã¢â‚¬Å’Ã™ÂÃ˜Â±Ã˜Â¶ Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â´Ã˜Â± Ã™â€¦Ã›Å’Ã¢â‚¬Å’Ã˜Â´Ã™Ë†Ã™â€ Ã˜Â¯Ã˜â€º Ã™â€¦Ã˜Â¯Ã›Å’Ã˜Â± Ã™â€¦Ã›Å’Ã¢â‚¬Å’Ã˜ÂªÃ™Ë†Ã˜Â§Ã™â€ Ã˜Â¯ Ã˜Â¯Ã˜Â³Ã˜ÂªÃ™â€¡Ã¢â‚¬Å’Ã˜Â¨Ã™â€ Ã˜Â¯Ã›Å’Ã˜Å’ Ã˜ÂªÃ›Å’Ã˜ÂªÃ˜Â± Ã™ÂÃ˜Â§Ã˜Â±Ã˜Â³Ã›Å’Ã˜Å’ Ã˜Â®Ã™â€žÃ˜Â§Ã˜ÂµÃ™â€¡Ã˜Å’ Ã™â€¦Ã˜ÂªÃ™â€  Ã™â€šÃ˜Â§Ã˜Â¨Ã™â€ž Ã™â€ Ã™â€¦Ã˜Â§Ã›Å’Ã˜Â´Ã˜Å’ Ã˜ÂªÃ˜ÂµÃ™Ë†Ã›Å’Ã˜Â± Ã™â€¦Ã˜Â­Ã™â€žÃ›Å’ Ã™Ë† Ã™Ë†Ã˜Â¶Ã˜Â¹Ã›Å’Ã˜Âª Ã˜Â§Ã™â€ Ã˜ÂªÃ˜Â´Ã˜Â§Ã˜Â± Ã˜Â±Ã˜Â§ Ã™Ë†Ã›Å’Ã˜Â±Ã˜Â§Ã›Å’Ã˜Â´ ÃšÂ©Ã™â€ Ã˜Â¯.</p>

<div class="grid desktop-grid-3" style="margin-top:16px;">
    <x-stat-card label="ÃšÂ©Ã™â€ž Ã˜Â®Ã˜Â¨Ã˜Â±Ã™â€¡Ã˜Â§" :value="$articles->count()" hint="Ã˜Â¢Ã˜Â®Ã˜Â±Ã›Å’Ã™â€  Ã›ÂµÃ›Â° Ã™â€¦Ã™Ë†Ã˜Â±Ã˜Â¯" />
    <x-stat-card label="Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â´Ã˜Â±Ã˜Â´Ã˜Â¯Ã™â€¡" :value="$articles->where('status', 'published')->count()" hint="Ã™â€šÃ˜Â§Ã˜Â¨Ã™â€ž Ã™â€ Ã™â€¦Ã˜Â§Ã›Å’Ã˜Â´" />
    <x-stat-card label="Ã˜ÂªÃ˜Â±Ã˜Â¬Ã™â€¦Ã™â€¡Ã¢â‚¬Å’Ã˜Â´Ã˜Â¯Ã™â€¡" :value="$articles->whereNotNull('translated_title')->count()" hint="Ã˜Â¨Ã˜Â§ Ã™â€¦Ã˜ÂªÃ˜Â±Ã˜Â¬Ã™â€¦" />
</div>

<x-ui.card style="margin-top:16px;">
    <div class="admin-user-row">
        <div>
            <h2 class="section-title" style="margin-top:0;">Ã™â€¡Ã™â€¦ÃšÂ¯Ã˜Â§Ã™â€¦Ã¢â‚¬Å’Ã˜Â³Ã˜Â§Ã˜Â²Ã›Å’ Ã˜Â¯Ã˜Â³Ã˜ÂªÃ›Å’</h2>
            <p class="muted">Ã˜Â¨Ã˜Â±Ã˜Â§Ã›Å’ Ã˜Â§Ã˜Â¬Ã˜Â±Ã˜Â§Ã›Å’ Ã˜Â®Ã™Ë†Ã˜Â¯ÃšÂ©Ã˜Â§Ã˜Â± Ã™â€¡Ã˜Â± Ã›Å’ÃšÂ© Ã˜Â³Ã˜Â§Ã˜Â¹Ã˜ÂªÃ˜Å’ ÃšÂ©Ã˜Â±Ã™Ë†Ã™â€  Laravel Scheduler Ã˜Â¨Ã˜Â§Ã›Å’Ã˜Â¯ Ã™ÂÃ˜Â¹Ã˜Â§Ã™â€ž Ã˜Â¨Ã˜Â§Ã˜Â´Ã˜Â¯.</p>
        </div>
        <div class="admin-action-row">
            <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.sync' : 'admin.news.sync') }}">@csrf<button class="btn btn-primary" type="submit">Ã˜Â¯Ã˜Â±Ã›Å’Ã˜Â§Ã™ÂÃ˜Âª Ã˜Â®Ã˜Â¨Ã˜Â±Ã™â€¡Ã˜Â§ Ã™Ë† Ã˜Â¯Ã˜Â§Ã™â€ Ã™â€žÃ™Ë†Ã˜Â¯ Ã˜ÂªÃ˜ÂµÃ˜Â§Ã™Ë†Ã›Å’Ã˜Â±</button></form>
            <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.test-gemini' : 'admin.news.test-gemini') }}">@csrf<button class="btn btn-outline" type="submit">Ã˜ÂªÃ˜Â³Ã˜Âª Gemini</button></form>
            <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.test-llm7' : 'admin.news.test-llm7') }}">@csrf<button class="btn btn-outline" type="submit">Test LLM7</button></form>
            <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.test-microsoft' : 'admin.news.test-microsoft') }}">@csrf<button class="btn btn-outline" type="submit">Ã˜ÂªÃ˜Â³Ã˜Âª Microsoft</button></form>
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
                    <span>Ã˜Â¨Ã˜Â¯Ã™Ë†Ã™â€  Ã˜ÂªÃ˜ÂµÃ™Ë†Ã›Å’Ã˜Â±</span>
                @endif
            </div>
            <div class="admin-news-fields">
                <div class="grid desktop-grid-2">
                    <div class="field"><label>Ã˜ÂªÃ›Å’Ã˜ÂªÃ˜Â± Ã™ÂÃ˜Â§Ã˜Â±Ã˜Â³Ã›Å’</label><input class="input" name="translated_title" value="{{ $article->translated_title ?: $article->original_title }}"></div>
                    <div class="field"><label>Ã˜Â¯Ã˜Â³Ã˜ÂªÃ™â€¡Ã¢â‚¬Å’Ã˜Â¨Ã™â€ Ã˜Â¯Ã›Å’</label><select class="input" name="category_id"><option value="">Ã˜Â¨Ã˜Â¯Ã™Ë†Ã™â€  Ã˜Â¯Ã˜Â³Ã˜ÂªÃ™â€¡</option>@foreach(($categories ?? collect()) as $cat)<option value="{{ $cat->id }}" @selected($article->category_id === $cat->id)>{{ $cat->title }}</option>@endforeach</select></div>
                </div>
                <div class="field"><label>Ã˜Â®Ã™â€žÃ˜Â§Ã˜ÂµÃ™â€¡ Ã™ÂÃ˜Â§Ã˜Â±Ã˜Â³Ã›Å’</label><textarea class="input" name="translated_summary" style="height:82px;padding-top:10px;">{{ $article->translated_summary ?: $article->original_description }}</textarea></div>
                <div class="field"><label>Ã™â€¦Ã˜ÂªÃ™â€  Ã™â€šÃ˜Â§Ã˜Â¨Ã™â€ž Ã™â€ Ã™â€¦Ã˜Â§Ã›Å’Ã˜Â´ Ã˜Â¯Ã˜Â± Ã˜ÂµÃ™ÂÃ˜Â­Ã™â€¡ Ã˜Â®Ã˜Â¨Ã˜Â±</label><textarea class="input" name="translated_body" style="height:130px;padding-top:10px;">{{ $article->translated_body ?: $article->original_content }}</textarea></div>
                <div class="grid desktop-grid-3">
                    <div class="field"><label>Ã™Ë†Ã˜Â¶Ã˜Â¹Ã›Å’Ã˜Âª</label><select class="input" name="status"><option value="published" @selected($article->status === 'published')>Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â´Ã˜Â± Ã˜Â´Ã˜Â¯Ã™â€¡</option><option value="draft" @selected($article->status === 'draft')>Ã™Â¾Ã›Å’Ã˜Â´Ã¢â‚¬Å’Ã™â€ Ã™Ë†Ã›Å’Ã˜Â³</option><option value="hidden" @selected($article->status === 'hidden')>Ã™â€¦Ã˜Â®Ã™ÂÃ›Å’</option></select></div>
                    <div class="field"><label>Ã˜ÂªÃ˜ÂµÃ™Ë†Ã›Å’Ã˜Â± Ã™â€¦Ã˜Â­Ã™â€žÃ›Å’ Ã˜Â¬Ã˜Â¯Ã›Å’Ã˜Â¯</label><input class="input" type="file" name="local_image_file" accept="image/*"></div>
                    <div class="field"><label>Ã™â€žÃ›Å’Ã™â€ ÃšÂ© Ã™â€¦Ã™â€ Ã˜Â¨Ã˜Â¹</label><input class="input" dir="ltr" name="original_url" value="{{ $article->original_url }}"></div>
                </div>
                <label class="toggle-row compact"><input type="checkbox" name="is_featured" value="1" @checked($article->is_featured)> <span>Ã˜Â®Ã˜Â¨Ã˜Â± Ã™Ë†Ã›Å’ÃšËœÃ™â€¡</span></label>
                <div class="muted small" style="line-height:1.8;">Ã™â€¦Ã™â€ Ã˜Â¨Ã˜Â¹: {{ $article->safe_source_name }} Ã‚Â· Ã˜ÂªÃ˜Â§Ã˜Â±Ã›Å’Ã˜Â®: {{ \App\Support\Jalali::format($article->published_at, 'Y/m/d H:i') }} Ã‚Â· Ã˜ÂªÃ›Å’Ã˜ÂªÃ˜Â± Ã˜Â§Ã˜ÂµÃ™â€žÃ›Å’: {{ $article->original_title }}</div>
                <div class="admin-action-row"><button class="btn btn-primary" type="submit">Ã˜Â°Ã˜Â®Ã›Å’Ã˜Â±Ã™â€¡ Ã˜Â®Ã˜Â¨Ã˜Â±</button><a class="btn btn-outline" target="_blank" href="{{ \Illuminate\Support\Facades\Route::has('public.news.show') ? route('public.news.show', $article) : '#' }}">Ã™â€ Ã™â€¦Ã˜Â§Ã›Å’Ã˜Â´</a></div>
            </div>
        </form>
        <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.destroy' : 'admin.news.destroy', $article) }}" onsubmit="return confirm('Ã˜Â§Ã›Å’Ã™â€  Ã˜Â®Ã˜Â¨Ã˜Â± Ã˜Â­Ã˜Â°Ã™Â Ã˜Â´Ã™Ë†Ã˜Â¯Ã˜Å¸')" style="margin-top:10px;">
            @csrf @method('DELETE')
            <button class="btn btn-soft" type="submit">Ã˜Â­Ã˜Â°Ã™Â Ã˜Â®Ã˜Â¨Ã˜Â±</button>
        </form>
    </x-ui.card>
@empty
    <x-ui.card><p class="muted">Ã™â€¡Ã™â€ Ã™Ë†Ã˜Â² Ã˜Â®Ã˜Â¨Ã˜Â±Ã›Å’ Ã˜Â¯Ã˜Â±Ã›Å’Ã˜Â§Ã™ÂÃ˜Âª Ã™â€ Ã˜Â´Ã˜Â¯Ã™â€¡ Ã˜Â§Ã˜Â³Ã˜Âª.</p></x-ui.card>
@endforelse
</div>

<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">Ã˜Â¢Ã˜Â®Ã˜Â±Ã›Å’Ã™â€  Ã™â€žÃ˜Â§ÃšÂ¯Ã¢â‚¬Å’Ã™â€¡Ã˜Â§</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Ã™Ë†Ã˜Â¶Ã˜Â¹Ã›Å’Ã˜Âª</th><th>Ã˜Â¯Ã˜Â±Ã›Å’Ã˜Â§Ã™ÂÃ˜ÂªÃ›Å’</th><th>Ã˜Â§Ã›Å’Ã˜Â¬Ã˜Â§Ã˜Â¯</th><th>Ã˜Â¨Ã™â€¡Ã¢â‚¬Å’Ã˜Â±Ã™Ë†Ã˜Â²Ã˜Â±Ã˜Â³Ã˜Â§Ã™â€ Ã›Å’</th><th>Ã˜ÂªÃ˜Â±Ã˜Â¬Ã™â€¦Ã™â€¡</th><th>Ã™Â¾Ã›Å’Ã˜Â§Ã™â€¦</th><th>Ã˜Â²Ã™â€¦Ã˜Â§Ã™â€ </th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr><td>{{ $log->status }}</td><td>{{ $log->items_received }}</td><td>{{ $log->items_created }}</td><td>{{ $log->items_updated }}</td><td>{{ $log->items_translated }}</td><td>{{ $log->message }}</td><td>{{ \App\Support\Jalali::format($log->created_at, 'Y/m/d H:i') }}</td></tr>
                @empty
                    <tr><td colspan="7" class="muted">Ã™â€žÃ˜Â§ÃšÂ¯Ã›Å’ Ã˜Â«Ã˜Â¨Ã˜Âª Ã™â€ Ã˜Â´Ã˜Â¯Ã™â€¡ Ã˜Â§Ã˜Â³Ã˜Âª.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
