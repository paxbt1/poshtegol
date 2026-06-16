@extends('layouts.admin')

@section('content')
<h1 class="title">تنظیمات سیستم</h1>
<p class="muted" style="line-height:1.8;margin-top:8px;">در این بخش تنظیمات اصلی دسترسی، قفل پیش‌بینی، رفتار کاربران ناشناس و اتصال به football-data، اخبار، Gemini و درگاه پرداخت مدیریت می‌شود.</p>

<div class="grid desktop-grid-2" style="margin-top:16px;">
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">تنظیمات عمومی و امنیت دسترسی</h2>
        <form data-ajax method="POST" action="{{ route('admin.settings.general.update') }}">
            @csrf
            <div class="field">
                <label>عنوان اپلیکیشن</label>
                <input class="input" name="app_title" value="{{ $settings['app_title'] }}" required>
                <div class="form-error" data-error-for="app_title"></div>
            </div>
            <div class="field">
                <label>رفتار کاربر بدون لینک دعوت مادر</label>
                <select class="input" name="unauthorized_access_mode">
                    <option value="redirect" @selected($settings['unauthorized_access_mode'] === 'redirect')>ریدایرکت به سایت دیگر</option>
                    <option value="404" @selected($settings['unauthorized_access_mode'] === '404')>نمایش خطای ۴۰۴</option>
                </select>
                <div class="form-error" data-error-for="unauthorized_access_mode"></div>
            </div>
            <div class="field">
                <label>آدرس ریدایرکت کاربران غیرمجاز</label>
                <input class="input" dir="ltr" name="unauthorized_redirect_url" value="{{ $settings['unauthorized_redirect_url'] }}" placeholder="https://poshtegol.ir">
                <div class="form-error" data-error-for="unauthorized_redirect_url"></div>
            </div>
            <div class="field">
                <label>قفل پیش‌بینی چند دقیقه قبل از شروع بازی؟</label>
                <input class="input" name="prediction_lock_minutes" inputmode="numeric" value="{{ $settings['prediction_lock_minutes'] }}" required>
                <div class="form-error" data-error-for="prediction_lock_minutes"></div>
            </div>
            <label class="toggle-row">
                <input type="checkbox" name="enable_half_time_markets" value="1" @checked($settings['enable_half_time_markets'])>
                <span>فعال‌سازی بازارهای نیمه اول/نیمه دوم در توسعه‌های بعدی</span>
            </label>
            <button class="btn btn-primary w-full" type="submit" style="margin-top:16px;">ذخیره تنظیمات عمومی</button>
        </form>
    </x-ui.card>

    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">تنظیمات football-data</h2>
        <form data-ajax method="POST" action="{{ route('admin.settings.football-data.update') }}">
            @csrf
            <label class="toggle-row">
                <input type="checkbox" name="football_data_enabled" value="1" @checked($settings['football_data_enabled'])>
                <span>فعال بودن همگام‌سازی وب‌سرویس</span>
            </label>
            <div class="field">
                <label>Base URL</label>
                <input class="input" dir="ltr" name="football_data_base_url" value="{{ $settings['football_data_base_url'] }}" required>
                <div class="form-error" data-error-for="football_data_base_url"></div>
            </div>
            <div class="field">
                <label>API Token</label>
                <input class="input" dir="ltr" name="football_data_api_token" value="{{ $settings['football_data_api_token'] }}" placeholder="توکن دریافتی از football-data.org">
                <div class="form-error" data-error-for="football_data_api_token"></div>
            </div>
            <div class="grid grid-2">
                <div class="field">
                    <label>کد رقابت</label>
                    <input class="input" dir="ltr" name="football_data_competition_code" value="{{ $settings['football_data_competition_code'] }}" required>
                    <div class="form-error" data-error-for="football_data_competition_code"></div>
                </div>
                <div class="field">
                    <label>فصل</label>
                    <input class="input" name="football_data_season" inputmode="numeric" value="{{ $settings['football_data_season'] }}" required>
                    <div class="form-error" data-error-for="football_data_season"></div>
                </div>
            </div>
            <div class="field">
                <label>Timeout درخواست‌ها / ثانیه</label>
                <input class="input" name="football_data_timeout" inputmode="numeric" value="{{ $settings['football_data_timeout'] }}" required>
                <div class="form-error" data-error-for="football_data_timeout"></div>
            </div>
            <label class="toggle-row">
                <input type="checkbox" name="football_data_download_crests" value="1" @checked($settings['football_data_download_crests'])>
                <span>دانلود و ذخیره محلی لوگوی تیم‌ها</span>
            </label>
            <button class="btn btn-primary w-full" type="submit" style="margin-top:16px;">ذخیره تنظیمات وب‌سرویس</button>
        </form>
    </x-ui.card>
</div>


<div class="grid desktop-grid-2" style="margin-top:16px;">
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">تنظیمات اخبار و ترجمه</h2>
        <form data-ajax method="POST" action="{{ route('admin.settings.news.update') }}">
            @csrf
            <label class="toggle-row">
                <input type="checkbox" name="news_enabled" value="1" @checked($settings['news_enabled'])>
                <span>نمایش و همگام‌سازی خبرها در داشبورد کاربر</span>
            </label>
            <input type="hidden" name="news_provider" value="gnews">
            <div class="field">
                <label>GNews Base URL</label>
                <input class="input" dir="ltr" name="gnews_base_url" value="{{ $settings['gnews_base_url'] }}" required>
                <div class="form-error" data-error-for="gnews_base_url"></div>
            </div>
            <div class="field">
                <label>GNews API Key</label>
                <input class="input" dir="ltr" name="gnews_api_key" value="{{ $settings['gnews_api_key'] }}" placeholder="کلید جدید را وارد کنید یا مقدار فعلی را دست نزنید">
                <div class="form-error" data-error-for="gnews_api_key"></div>
            </div>
            <div class="field">
                <label>سرویس ترجمه پیش‌فرض</label>
                <select class="input" name="news_translation_provider">
                    <option value="gemini" @selected($settings['news_translation_provider'] === 'gemini')>Gemini / ترجمه هوشمند و خلاصه‌سازی بهتر</option>
                    <option value="llm7" @selected($settings['news_translation_provider'] === 'llm7')>LLM7 / جایگزین هوشمند</option>
                    <option value="microsoft" @selected($settings['news_translation_provider'] === 'microsoft')>Microsoft Translator / جایگزین پایدار</option>
                </select>
                <div class="form-error" data-error-for="news_translation_provider"></div>
            </div>
            <div class="field">
                <label>Gemini API Key</label>
                <input class="input" dir="ltr" name="gemini_api_key" value="{{ $settings['gemini_api_key'] }}" placeholder="کلید جدید را وارد کنید یا مقدار فعلی را دست نزنید">
                <div class="form-error" data-error-for="gemini_api_key"></div>
            </div>
            <div class="field">
                <label>مدل Gemini</label>
                <input class="input" dir="ltr" name="gemini_model" value="{{ $settings['gemini_model'] }}" required>
                <div class="form-error" data-error-for="gemini_model"></div>
            </div>
            <div class="field">
                <label>LLM7 API Key</label>
                <input class="input" dir="ltr" name="llm7_api_key" value="{{ $settings['llm7_api_key'] }}" placeholder="Enter a new key or leave unchanged">
                <div class="form-error" data-error-for="llm7_api_key"></div>
            </div>
            <div class="grid grid-2">
                <div class="field">
                    <label>LLM7 Base URL</label>
                    <input class="input" dir="ltr" name="llm7_base_url" value="{{ $settings['llm7_base_url'] }}" required>
                    <div class="form-error" data-error-for="llm7_base_url"></div>
                </div>
                <div class="field">
                    <label>LLM7 Model</label>
                    <input class="input" dir="ltr" name="llm7_model" value="{{ $settings['llm7_model'] }}" required>
                    <div class="form-error" data-error-for="llm7_model"></div>
                </div>
            </div>
            <div class="field">
                <label>Microsoft Translator Key</label>
                <input class="input" dir="ltr" name="microsoft_translator_key" value="{{ $settings['microsoft_translator_key'] }}" placeholder="کلید جدید را وارد کنید یا مقدار فعلی را دست نزنید">
                <div class="form-error" data-error-for="microsoft_translator_key"></div>
            </div>
            <div class="grid grid-2">
                <div class="field">
                    <label>Microsoft Region / اختیاری</label>
                    <input class="input" dir="ltr" name="microsoft_translator_region" value="{{ $settings['microsoft_translator_region'] }}" placeholder="مثلاً westeurope">
                    <div class="form-error" data-error-for="microsoft_translator_region"></div>
                </div>
                <div class="field">
                    <label>Microsoft Endpoint</label>
                    <input class="input" dir="ltr" name="microsoft_translator_endpoint" value="{{ $settings['microsoft_translator_endpoint'] }}" required>
                    <div class="form-error" data-error-for="microsoft_translator_endpoint"></div>
                </div>
            </div>
            <div class="field">
                <label>عبارت جستجو</label>
                <input class="input" dir="ltr" name="news_query" value="{{ $settings['news_query'] }}" required>
                <div class="form-error" data-error-for="news_query"></div>
            </div>
            <div class="grid grid-2">
                <div class="field">
                    <label>زبان خبر</label>
                    <input class="input" dir="ltr" name="news_language" value="{{ $settings['news_language'] }}" required>
                    <div class="form-error" data-error-for="news_language"></div>
                </div>
                <div class="field">
                    <label>کشور / اختیاری</label>
                    <input class="input" dir="ltr" name="news_country" value="{{ $settings['news_country'] }}" placeholder="مثلاً us یا خالی">
                    <div class="form-error" data-error-for="news_country"></div>
                </div>
            </div>
            <div class="grid grid-2">
                <div class="field">
                    <label>تعداد خبر در هر همگام‌سازی</label>
                    <input class="input" name="news_max_per_sync" inputmode="numeric" value="{{ $settings['news_max_per_sync'] }}" required>
                    <div class="form-error" data-error-for="news_max_per_sync"></div>
                </div>
                <div class="field">
                    <label>دانلود محلی تصویر خبر</label>
                    <label class="toggle-row compact" style="margin-top:0;min-height:46px;">
                        <input type="checkbox" name="news_download_images" value="1" @checked($settings['news_download_images'])>
                        <span>فعال</span>
                    </label>
                    <div class="form-error" data-error-for="news_download_images"></div>
                </div>
            </div>
            <div class="grid grid-2">
                <div class="field">
                    <label>مرتب‌سازی</label>
                    <select class="input" name="news_sort_by">
                        <option value="publishedAt" @selected($settings['news_sort_by'] === 'publishedAt')>جدیدترین</option>
                        <option value="relevance" @selected($settings['news_sort_by'] === 'relevance')>مرتبط‌ترین</option>
                    </select>
                    <div class="form-error" data-error-for="news_sort_by"></div>
                </div>
            </div>
            <div class="field">
                <label>فیلدهای جستجو در GNews</label>
                <input class="input" dir="ltr" name="news_in_fields" value="{{ $settings['news_in_fields'] }}" placeholder="title,description">
                <div class="form-error" data-error-for="news_in_fields"></div>
            </div>
            <button class="btn btn-primary w-full" type="submit" style="margin-top:16px;">ذخیره تنظیمات اخبار و ترجمه</button>
        </form>

        <div class="grid grid-2" style="margin-top:12px;">
            <form data-ajax method="POST" action="{{ route('admin.news.test-gemini') }}">
                @csrf
                <button class="btn btn-outline w-full" type="submit">تست اتصال Gemini</button>
            </form>
            <form data-ajax method="POST" action="{{ route('admin.news.test-llm7') }}">
                @csrf
                <button class="btn btn-outline w-full" type="submit">تست اتصال LLM7</button>
            </form>
            <form data-ajax method="POST" action="{{ route('admin.news.test-microsoft') }}">
                @csrf
                <button class="btn btn-outline w-full" type="submit">تست اتصال Microsoft</button>
            </form>
        </div>
    </x-ui.card>

    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">مدل پرداخت توکنی</h2>
        <p class="muted" style="line-height:1.9;">درگاه آنلاین و پرداخت کارت به کارت از مسیر پیش‌بینی حذف شده است. کاربر هنگام ثبت پیش‌بینی تعداد توکن را مشخص می‌کند و تسویه ریالی بعد از پایان جام بر اساس خالص توکن بدهکار یا بستانکار انجام می‌شود.</p>
        <a class="btn btn-primary w-full" href="{{ route('admin.settings.finance') }}" style="margin-top:16px;">تنظیمات توکن‌ها</a>
    </x-ui.card>
</div>

<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">راهنمای سریع</h2>
    <div class="guide-list">
        <div><strong>لینک دعوت مادر</strong><span>برای کنترل دسترسی اولیه از بخش «دعوت‌ها» لینک مادر بسازید. کاربران مستقیم به سایت، طبق همین تنظیمات ریدایرکت یا ۴۰۴ می‌شوند.</span></div>
        <div><strong>football-data</strong><span>بعد از ذخیره توکن، از منوی «همگام‌سازی بازی‌ها» ابتدا تیم‌ها، سپس برنامه بازی‌ها و در نهایت نتایج را همگام کنید.</span></div>
        <div><strong>قفل پیش‌بینی</strong><span>تغییر زمان قفل، مهلت پیش‌بینی بازی‌های دارای زمان شروع را دوباره محاسبه می‌کند.</span></div>
    </div>
</x-ui.card>
@endsection
