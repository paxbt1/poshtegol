@extends('layouts.news-admin')

@section('content')
<h1 class="title">تنظیمات سایت خبری پشت گل</h1>
<p class="muted" style="line-height:1.9;margin-top:8px;">تنظیمات وب‌سرویس‌های خبری، مترجم‌ها، هویت سایت و فوتر از این مسیر مستقل مدیریت می‌شود.</p>

<div class="grid desktop-grid-2" style="margin-top:16px;">
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">هویت سایت و تبلیغات</h2>
        <form data-ajax method="POST" action="{{ route('news-admin.settings.site.update') }}">
            @csrf
            <div class="field"><label>نام سایت</label><input class="input" name="public_site_name" value="{{ $settings['public_site_name'] }}" required><div class="form-error" data-error-for="public_site_name"></div></div>
            <div class="field"><label>زیرعنوان</label><input class="input" name="public_site_tagline" value="{{ $settings['public_site_tagline'] }}" required><div class="form-error" data-error-for="public_site_tagline"></div></div>
            <div class="field"><label>دامنه</label><input class="input" dir="ltr" name="public_site_domain" value="{{ $settings['public_site_domain'] }}" required><div class="form-error" data-error-for="public_site_domain"></div></div>
            <label class="toggle-row"><input type="checkbox" name="public_ads_enabled" value="1" @checked($settings['public_ads_enabled'])><span>نمایش جایگاه‌های تبلیغاتی</span></label>
            <button class="btn btn-primary w-full" type="submit" style="margin-top:16px;">ذخیره هویت سایت</button>
        </form>
    </x-ui.card>

    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">وب‌سرویس خبر و مترجم</h2>
        <form data-ajax method="POST" action="{{ route('news-admin.settings.news.update') }}">
            @csrf
            <label class="toggle-row"><input type="checkbox" name="news_enabled" value="1" @checked($settings['news_enabled'])><span>فعال بودن همگام‌سازی خبر</span></label>
            <input type="hidden" name="news_provider" value="gnews">
            <div class="field"><label>GNews Base URL</label><input class="input" dir="ltr" name="gnews_base_url" value="{{ $settings['gnews_base_url'] }}" required><div class="form-error" data-error-for="gnews_base_url"></div></div>
            <div class="field"><label>GNews API Key</label><input class="input" dir="ltr" name="gnews_api_key" value="{{ $settings['gnews_api_key'] }}" placeholder="کلید جدید را وارد کنید یا مقدار فعلی را دست نزنید"><div class="form-error" data-error-for="gnews_api_key"></div></div>
            <div class="field"><label>جستجوی عمومی خبر</label><input class="input" dir="ltr" name="news_query" value="{{ $settings['news_query'] }}" required><div class="form-error" data-error-for="news_query"></div></div>
            <div class="field"><label>مپ هوشمند جستجو برای دسته‌بندی‌ها / JSON</label><textarea class="input mono" dir="ltr" name="news_category_queries_json" style="height:150px;padding-top:10px;">{{ $settings['news_category_queries_json'] }}</textarea><div class="form-error" data-error-for="news_category_queries_json"></div></div>
            <div class="grid grid-2">
                <div class="field"><label>زبان خبر</label><input class="input" dir="ltr" name="news_language" value="{{ $settings['news_language'] }}" required></div>
                <div class="field"><label>کشور اختیاری</label><input class="input" dir="ltr" name="news_country" value="{{ $settings['news_country'] }}"></div>
                <div class="field"><label>تعداد در هر همگام‌سازی</label><input class="input" name="news_max_per_sync" inputmode="numeric" value="{{ $settings['news_max_per_sync'] }}" required></div>
                <div class="field"><label>مرتب‌سازی</label><select class="input" name="news_sort_by"><option value="publishedAt" @selected($settings['news_sort_by']==='publishedAt')>جدیدترین</option><option value="relevance" @selected($settings['news_sort_by']==='relevance')>مرتبط‌ترین</option></select></div>
            </div>
            <div class="field"><label>محدوده جستجو</label><input class="input" dir="ltr" name="news_in_fields" value="{{ $settings['news_in_fields'] }}"></div>
            <label class="toggle-row"><input type="checkbox" name="news_download_images" value="1" @checked($settings['news_download_images'])><span>دانلود تصاویر خبرها و لود محلی</span></label>
            <div class="field"><label>سرویس ترجمه پیش‌فرض</label><select class="input" name="news_translation_provider"><option value="gemini" @selected($settings['news_translation_provider']==='gemini')>Gemini</option><option value="microsoft" @selected($settings['news_translation_provider']==='microsoft')>Microsoft Translator</option></select></div>
            <div class="field"><label>Gemini API Key</label><input class="input" dir="ltr" name="gemini_api_key" value="{{ $settings['gemini_api_key'] }}"></div>
            <div class="field"><label>Gemini Model</label><input class="input" dir="ltr" name="gemini_model" value="{{ $settings['gemini_model'] }}" required></div>
            <div class="field"><label>Microsoft Translator Key</label><input class="input" dir="ltr" name="microsoft_translator_key" value="{{ $settings['microsoft_translator_key'] }}"></div>
            <div class="grid grid-2">
                <div class="field"><label>Region</label><input class="input" dir="ltr" name="microsoft_translator_region" value="{{ $settings['microsoft_translator_region'] }}"></div>
                <div class="field"><label>Endpoint</label><input class="input" dir="ltr" name="microsoft_translator_endpoint" value="{{ $settings['microsoft_translator_endpoint'] }}" required></div>
            </div>
            <button class="btn btn-primary w-full" type="submit" style="margin-top:16px;">ذخیره سرویس‌ها</button>
        </form>
    </x-ui.card>
</div>

<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">محتوای فوتر</h2>
    <form data-ajax method="POST" action="{{ route('news-admin.settings.footer.update') }}" class="grid desktop-grid-2">
        @csrf
        <div class="field" style="grid-column:1/-1;"><label>متن معرفی</label><textarea class="input" name="footer_about_text" style="height:90px;padding-top:10px;">{{ $settings['footer_about_text'] }}</textarea></div>
        @for($i=1;$i<=3;$i++)
            <div class="field"><label>عنوان ستون {{ $i }}</label><input class="input" name="footer_column_{{ $i }}_title" value="{{ $settings['footer_column_'.$i.'_title'] }}"></div>
            <div class="field"><label>متن ستون {{ $i }}</label><textarea class="input" name="footer_column_{{ $i }}_body" style="height:110px;padding-top:10px;">{{ $settings['footer_column_'.$i.'_body'] }}</textarea></div>
        @endfor
        <div class="field" style="grid-column:1/-1;"><label>متن کپی‌رایت</label><input class="input" name="footer_copyright_text" value="{{ $settings['footer_copyright_text'] }}"></div>
        <button class="btn btn-primary" type="submit" style="grid-column:1/-1;">ذخیره فوتر</button>
    </form>
</x-ui.card>
@endsection
