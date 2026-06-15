<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'مدیریت خبری پشت گل' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="admin-shell news-admin-shell">
        <aside class="admin-sidebar news-admin-sidebar">
            <div class="brand"><span class="brand-mark">پ</span><span>مدیریت پشت گل</span></div>
            <nav class="admin-links">
                <a href="{{ route('news-admin.dashboard') }}">داشبورد خبری</a>
                <a href="{{ route('news-admin.news.index') }}">اخبار</a>
                <a href="{{ route('news-admin.public.categories') }}">دسته‌بندی‌ها</a>
                <a href="{{ route('news-admin.public.ads') }}">تبلیغات</a>
                <a href="{{ route('news-admin.public.sources') }}">منابع خبر/داده</a>
                <a href="{{ route('news-admin.settings') }}">تنظیمات سایت و فوتر</a>
                <a href="{{ route('public.home') }}" target="_blank">مشاهده سایت</a>
                <a href="{{ route('admin.index') }}">مدیریت کاپ</a>
            </nav>
        </aside>
        <main class="app-shell">
            <div class="container wide-container">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
