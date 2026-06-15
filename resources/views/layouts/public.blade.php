<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? (($siteName ?? 'پشت گل').' | '.($siteTagline ?? 'خبر فوتبال')) }}</title>
    <meta name="description" content="{{ $description ?? 'پشت گل؛ خبر، نتیجه زنده، برنامه بازی‌ها و روایت فارسی فوتبال جهان با تمرکز روی جام جهانی ۲۰۲۶' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-body">
    <header class="pg-header">
        <div class="pg-container">
            <div class="pg-topbar">
                <a class="pg-brand" href="{{ route('public.home') }}" aria-label="پشت گل">
                    <span class="pg-brand-mark">پ</span>
                    <span><strong>{{ $siteName ?? 'پشت گل' }}</strong><em>{{ $siteTagline ?? 'خبر فوتبال' }}</em></span>
                </a>
                <form class="pg-search" method="GET" action="{{ route('public.search') }}">
                    <input name="q" value="{{ request('q') }}" placeholder="جستجو در اخبار فوتبال...">
                    <button type="submit">جستجو</button>
                </form>
            </div>
            <nav class="pg-nav" aria-label="منوی اصلی">
                <a href="{{ route('public.home') }}">خانه</a>
                <a href="{{ route('public.news') }}">اخبار</a>
                <a href="{{ route('public.live-scores') }}">نتایج زنده</a>
                <a href="{{ route('public.fixtures') }}">برنامه بازی‌ها</a>
                <a href="{{ route('public.videos') }}">ویدئوها</a>
                <a href="{{ route('public.teams') }}">تیم‌ها</a>
                @foreach(($categories ?? collect())->take(5) as $cat)
                    <a href="{{ route('public.category', $cat) }}">{{ $cat->title }}</a>
                @endforeach
            </nav>
            <x-public.ad-slot name="header_under_nav" :slots="$adSlots ?? collect()" />
        </div>
    </header>

    <main class="pg-main">
        <div class="pg-container pg-container-wide">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>

    @php
        $footerAbout = \App\Models\AppSetting::getValue('footer_about_text', 'پشت گل یک رسانه فارسی فوتبال است که اخبار خارجی را با لینک منبع اصلی بازنشر و خلاصه‌سازی می‌کند.');
        $footerCopyright = \App\Models\AppSetting::getValue('footer_copyright_text', '© پشت گل - تمامی حقوق قالب و گردآوری محفوظ است.');
    @endphp
    <footer class="pg-footer">
        <div class="pg-container pg-footer-grid">
            <div>
                <div class="pg-brand compact"><span class="pg-brand-mark">پ</span><span><strong>{{ $siteName ?? 'پشت گل' }}</strong><em>{{ $siteTagline ?? 'خبر فوتبال' }}</em></span></div>
                <p>{!! nl2br(e($footerAbout)) !!}</p>
            </div>
            @for($i = 1; $i <= 3; $i++)
                <div>
                    <strong>{{ \App\Models\AppSetting::getValue('footer_column_'.$i.'_title', $i === 1 ? 'بخش‌ها' : ($i === 2 ? 'تبلیغات و همکاری' : 'حقوق محتوا')) }}</strong>
                    <p>{!! nl2br(e(\App\Models\AppSetting::getValue('footer_column_'.$i.'_body', ''))) !!}</p>
                </div>
            @endfor
        </div>
        <div class="pg-container pg-footer-bottom">
            <p>{{ $footerCopyright }}</p>
            <p>تمام اخبار از منابع خارجی کپی/بازنشر شده‌اند و لینک منبع اصلی در انتهای هر خبر درج می‌شود.</p>
        </div>
    </footer>
</body>
</html>
