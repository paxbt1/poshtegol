<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? \App\Models\AppSetting::getValue('app_title', 'کاپ خانوادگی') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="brand"><span class="brand-mark">ک</span><span>کاپ خانوادگی</span></div>
            <nav class="admin-links">
                <a href="{{ route('admin.index') }}">داشبورد کاپ</a>
                <a href="{{ route('admin.matches') }}">بازی‌ها</a>
                <a href="{{ route('admin.users') }}">کاربران</a>
                <a href="{{ route('admin.predictions') }}">پیش‌بینی‌ها</a>
                <a href="{{ route('admin.payments') }}">پرداخت‌ها</a>
                <a href="{{ route('admin.referrals') }}">دعوت‌ها</a>
                <a href="{{ route('admin.settings.finance') }}">مالی کاپ</a>
                <a href="{{ route('admin.finance.report') }}">گزارش مالی</a>
                <a href="{{ route('admin.football-data.index') }}">همگام‌سازی بازی‌ها</a>
                <a href="{{ route('admin.settlements') }}">تسویه‌ها</a>
                <a href="{{ route('admin.settings') }}">تنظیمات کاپ</a>
                <a href="{{ route('news-admin.dashboard') }}">مدیریت خبری</a>
            </nav>
        </aside>
        <main class="app-shell">
            <div class="container">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
