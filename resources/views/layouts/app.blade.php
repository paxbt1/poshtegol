<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? \App\Models\AppSetting::getValue('app_title', 'کاپ خانوادگی جام جهانی ۲۰۲۶') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <div class="container">
            <x-top-nav />
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </div>
    <x-bottom-nav />
</body>
</html>
