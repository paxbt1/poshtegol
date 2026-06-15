@extends('layouts.news-admin')

@section('content')
<h1 class="title">ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø³Ø§ÛŒØª Ø®Ø¨Ø±ÛŒ Ù¾Ø´Øª Ú¯Ù„</h1>
<p class="muted" style="line-height:1.9;margin-top:8px;">ØªÙ†Ø¸ÛŒÙ…Ø§Øª ÙˆØ¨â€ŒØ³Ø±ÙˆÛŒØ³â€ŒÙ‡Ø§ÛŒ Ø®Ø¨Ø±ÛŒØŒ Ù…ØªØ±Ø¬Ù…â€ŒÙ‡Ø§ØŒ Ù‡ÙˆÛŒØª Ø³Ø§ÛŒØª Ùˆ ÙÙˆØªØ± Ø§Ø² Ø§ÛŒÙ† Ù…Ø³ÛŒØ± Ù…Ø³ØªÙ‚Ù„ Ù…Ø¯ÛŒØ±ÛŒØª Ù…ÛŒâ€ŒØ´ÙˆØ¯.</p>

<div class="grid desktop-grid-2" style="margin-top:16px;">
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">Ù‡ÙˆÛŒØª Ø³Ø§ÛŒØª Ùˆ ØªØ¨Ù„ÛŒØºØ§Øª</h2>
        <form data-ajax method="POST" action="{{ route('news-admin.settings.site.update') }}">
            @csrf
            <div class="field"><label>Ù†Ø§Ù… Ø³Ø§ÛŒØª</label><input class="input" name="public_site_name" value="{{ $settings['public_site_name'] }}" required><div class="form-error" data-error-for="public_site_name"></div></div>
            <div class="field"><label>Ø²ÛŒØ±Ø¹Ù†ÙˆØ§Ù†</label><input class="input" name="public_site_tagline" value="{{ $settings['public_site_tagline'] }}" required><div class="form-error" data-error-for="public_site_tagline"></div></div>
            <div class="field"><label>Ø¯Ø§Ù…Ù†Ù‡</label><input class="input" dir="ltr" name="public_site_domain" value="{{ $settings['public_site_domain'] }}" required><div class="form-error" data-error-for="public_site_domain"></div></div>
            <label class="toggle-row"><input type="checkbox" name="public_ads_enabled" value="1" @checked($settings['public_ads_enabled'])><span>Ù†Ù…Ø§ÛŒØ´ Ø¬Ø§ÛŒÚ¯Ø§Ù‡â€ŒÙ‡Ø§ÛŒ ØªØ¨Ù„ÛŒØºØ§ØªÛŒ</span></label>
            <button class="btn btn-primary w-full" type="submit" style="margin-top:16px;">Ø°Ø®ÛŒØ±Ù‡ Ù‡ÙˆÛŒØª Ø³Ø§ÛŒØª</button>
        </form>
    </x-ui.card>

    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">ÙˆØ¨â€ŒØ³Ø±ÙˆÛŒØ³ Ø®Ø¨Ø± Ùˆ Ù…ØªØ±Ø¬Ù…</h2>
        <form data-ajax method="POST" action="{{ route('news-admin.settings.news.update') }}">
            @csrf
            <label class="toggle-row"><input type="checkbox" name="news_enabled" value="1" @checked($settings['news_enabled'])><span>ÙØ¹Ø§Ù„ Ø¨ÙˆØ¯Ù† Ù‡Ù…Ú¯Ø§Ù…â€ŒØ³Ø§Ø²ÛŒ Ø®Ø¨Ø±</span></label>
            <input type="hidden" name="news_provider" value="gnews">
            <div class="field"><label>GNews Base URL</label><input class="input" dir="ltr" name="gnews_base_url" value="{{ $settings['gnews_base_url'] }}" required><div class="form-error" data-error-for="gnews_base_url"></div></div>
            <div class="field"><label>GNews API Key</label><input class="input" dir="ltr" name="gnews_api_key" value="{{ $settings['gnews_api_key'] }}" placeholder="Ú©Ù„ÛŒØ¯ Ø¬Ø¯ÛŒØ¯ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯ ÛŒØ§ Ù…Ù‚Ø¯Ø§Ø± ÙØ¹Ù„ÛŒ Ø±Ø§ Ø¯Ø³Øª Ù†Ø²Ù†ÛŒØ¯"><div class="form-error" data-error-for="gnews_api_key"></div></div>
            <div class="field"><label>Ø¬Ø³ØªØ¬ÙˆÛŒ Ø¹Ù…ÙˆÙ…ÛŒ Ø®Ø¨Ø±</label><input class="input" dir="ltr" name="news_query" value="{{ $settings['news_query'] }}" required><div class="form-error" data-error-for="news_query"></div></div>
            <div class="field"><label>Ù…Ù¾ Ù‡ÙˆØ´Ù…Ù†Ø¯ Ø¬Ø³ØªØ¬Ùˆ Ø¨Ø±Ø§ÛŒ Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒâ€ŒÙ‡Ø§ / JSON</label><textarea class="input mono" dir="ltr" name="news_category_queries_json" style="height:150px;padding-top:10px;">{{ $settings['news_category_queries_json'] }}</textarea><div class="form-error" data-error-for="news_category_queries_json"></div></div>
            <div class="grid grid-2">
                <div class="field"><label>Ø²Ø¨Ø§Ù† Ø®Ø¨Ø±</label><input class="input" dir="ltr" name="news_language" value="{{ $settings['news_language'] }}" required></div>
                <div class="field"><label>Ú©Ø´ÙˆØ± Ø§Ø®ØªÛŒØ§Ø±ÛŒ</label><input class="input" dir="ltr" name="news_country" value="{{ $settings['news_country'] }}"></div>
                <div class="field"><label>ØªØ¹Ø¯Ø§Ø¯ Ø¯Ø± Ù‡Ø± Ù‡Ù…Ú¯Ø§Ù…â€ŒØ³Ø§Ø²ÛŒ</label><input class="input" name="news_max_per_sync" inputmode="numeric" value="{{ $settings['news_max_per_sync'] }}" required></div>
                <div class="field"><label>Ù…Ø±ØªØ¨â€ŒØ³Ø§Ø²ÛŒ</label><select class="input" name="news_sort_by"><option value="publishedAt" @selected($settings['news_sort_by']==='publishedAt')>Ø¬Ø¯ÛŒØ¯ØªØ±ÛŒÙ†</option><option value="relevance" @selected($settings['news_sort_by']==='relevance')>Ù…Ø±ØªØ¨Ø·â€ŒØªØ±ÛŒÙ†</option></select></div>
            </div>
            <div class="field"><label>Ù…Ø­Ø¯ÙˆØ¯Ù‡ Ø¬Ø³ØªØ¬Ùˆ</label><input class="input" dir="ltr" name="news_in_fields" value="{{ $settings['news_in_fields'] }}"></div>
            <label class="toggle-row"><input type="checkbox" name="news_download_images" value="1" @checked($settings['news_download_images'])><span>Ø¯Ø§Ù†Ù„ÙˆØ¯ ØªØµØ§ÙˆÛŒØ± Ø®Ø¨Ø±Ù‡Ø§ Ùˆ Ù„ÙˆØ¯ Ù…Ø­Ù„ÛŒ</span></label>
            <div class="field"><label>Ø³Ø±ÙˆÛŒØ³ ØªØ±Ø¬Ù…Ù‡ Ù¾ÛŒØ´â€ŒÙØ±Ø¶</label><select class="input" name="news_translation_provider"><option value="gemini" @selected($settings['news_translation_provider']==='gemini')>Gemini</option><option value="llm7" @selected($settings['news_translation_provider']==='llm7')>LLM7</option><option value="microsoft" @selected($settings['news_translation_provider']==='microsoft')>Microsoft Translator</option></select></div>
            <div class="field"><label>Gemini API Key</label><input class="input" dir="ltr" name="gemini_api_key" value="{{ $settings['gemini_api_key'] }}"></div>
            <div class="field"><label>Gemini Model</label><input class="input" dir="ltr" name="gemini_model" value="{{ $settings['gemini_model'] }}" required></div>
            <div class="field"><label>LLM7 API Key</label><input class="input" dir="ltr" name="llm7_api_key" value="{{ $settings['llm7_api_key'] }}"></div>
            <div class="grid grid-2">
                <div class="field"><label>LLM7 Base URL</label><input class="input" dir="ltr" name="llm7_base_url" value="{{ $settings['llm7_base_url'] }}" required></div>
                <div class="field"><label>LLM7 Model</label><input class="input" dir="ltr" name="llm7_model" value="{{ $settings['llm7_model'] }}" required></div>
            </div>
            <div class="field"><label>Microsoft Translator Key</label><input class="input" dir="ltr" name="microsoft_translator_key" value="{{ $settings['microsoft_translator_key'] }}"></div>
            <div class="grid grid-2">
                <div class="field"><label>Region</label><input class="input" dir="ltr" name="microsoft_translator_region" value="{{ $settings['microsoft_translator_region'] }}"></div>
                <div class="field"><label>Endpoint</label><input class="input" dir="ltr" name="microsoft_translator_endpoint" value="{{ $settings['microsoft_translator_endpoint'] }}" required></div>
            </div>
            <button class="btn btn-primary w-full" type="submit" style="margin-top:16px;">Ø°Ø®ÛŒØ±Ù‡ Ø³Ø±ÙˆÛŒØ³â€ŒÙ‡Ø§</button>
        </form>
    </x-ui.card>
</div>

<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">Ù…Ø­ØªÙˆØ§ÛŒ ÙÙˆØªØ±</h2>
    <form data-ajax method="POST" action="{{ route('news-admin.settings.footer.update') }}" class="grid desktop-grid-2">
        @csrf
        <div class="field" style="grid-column:1/-1;"><label>Ù…ØªÙ† Ù…Ø¹Ø±ÙÛŒ</label><textarea class="input" name="footer_about_text" style="height:90px;padding-top:10px;">{{ $settings['footer_about_text'] }}</textarea></div>
        @for($i=1;$i<=3;$i++)
            <div class="field"><label>Ø¹Ù†ÙˆØ§Ù† Ø³ØªÙˆÙ† {{ $i }}</label><input class="input" name="footer_column_{{ $i }}_title" value="{{ $settings['footer_column_'.$i.'_title'] }}"></div>
            <div class="field"><label>Ù…ØªÙ† Ø³ØªÙˆÙ† {{ $i }}</label><textarea class="input" name="footer_column_{{ $i }}_body" style="height:110px;padding-top:10px;">{{ $settings['footer_column_'.$i.'_body'] }}</textarea></div>
        @endfor
        <div class="field" style="grid-column:1/-1;"><label>Ù…ØªÙ† Ú©Ù¾ÛŒâ€ŒØ±Ø§ÛŒØª</label><input class="input" name="footer_copyright_text" value="{{ $settings['footer_copyright_text'] }}"></div>
        <button class="btn btn-primary" type="submit" style="grid-column:1/-1;">Ø°Ø®ÛŒØ±Ù‡ ÙÙˆØªØ±</button>
    </form>
</x-ui.card>
@endsection
