@extends('layouts.admin')

@section('content')
<div class="grid desktop-grid-4">
    <x-stat-card label="تیم‌ها" :value="number_format($teamsCount)" hint="ثبت‌شده" />
    <x-stat-card label="بازی‌ها" :value="number_format($matchesCount)" hint="ثبت‌شده" />
    <x-stat-card label="بازی‌های زنده" :value="number_format($liveCount)" hint="در حال پیگیری" />
    <x-stat-card label="همگام‌سازی ناموفق" :value="number_format($failedCount)" hint="لاگ‌ها" />
</div>

<x-ui.card style="margin-top:16px;">
    <h1 class="section-title" style="margin-top:0;">همگام‌سازی بازی‌ها</h1>
    @unless($enabled)
        <div class="notice">همگام‌سازی football-data در تنظیمات غیرفعال است.</div>
    @endunless
    @unless($hasToken)
        <div class="notice">توکن football-data تنظیم نشده است. تا زمان تنظیم توکن، برنامه از داده seed داخلی استفاده می‌کند.</div>
    @endunless

    <div class="grid desktop-grid-4" style="margin-top:14px;">
        <form data-ajax method="POST" action="{{ route('admin.football-data.sync') }}">
            @csrf
            <input type="hidden" name="action" value="teams">
            <button class="btn btn-outline w-full" type="submit">همگام‌سازی تیم‌ها</button>
        </form>
        <form data-ajax method="POST" action="{{ route('admin.football-data.sync') }}">
            @csrf
            <input type="hidden" name="action" value="fixtures">
            <button class="btn btn-outline w-full" type="submit">همگام‌سازی برنامه بازی‌ها</button>
        </form>
        <form data-ajax method="POST" action="{{ route('admin.football-data.sync') }}">
            @csrf
            <input type="hidden" name="action" value="results">
            <button class="btn btn-outline w-full" type="submit">همگام‌سازی نتایج و امتیازدهی</button>
        </form>
        <form data-ajax method="POST" action="{{ route('admin.football-data.sync') }}">
            @csrf
            <input type="hidden" name="action" value="all">
            <button class="btn btn-primary w-full" type="submit">همگام‌سازی کامل</button>
        </form>
    </div>
<p class="muted small" style="margin-top:12px;line-height:1.8;">منبع اصلی تیم‌ها، لوگوها، برنامه بازی‌ها و نتایج football-data.org است. اگر توکن تنظیم نباشد یا سرویس پاسخ ندهد، داده‌های قبلی در دیتابیس حفظ می‌شود.</p>
</x-ui.card>

<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">آخرین وضعیت‌ها</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>نوع</th>
                    <th>وضعیت</th>
                    <th>دریافتی</th>
                    <th>ایجاد</th>
                    <th>به‌روزرسانی</th>
                    <th>پیام</th>
                    <th>زمان</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lastLogs as $log)
                    <tr>
                        <td>{{ $log->type }}</td>
                        <td>{{ $log->status === 'success' ? 'موفق' : ($log->status === 'partial' ? 'نیمه‌کامل' : 'ناموفق') }}</td>
                        <td>{{ number_format($log->items_received) }}</td>
                        <td>{{ number_format($log->items_created) }}</td>
                        <td>{{ number_format($log->items_updated) }}</td>
                        <td>{{ $log->message ?? '-' }}</td>
                        <td>{{ \App\Support\Jalali::format($log->created_at, 'Y/m/d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">هنوز همگام‌سازی اجرا نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
