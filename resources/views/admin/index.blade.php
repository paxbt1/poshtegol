@extends('layouts.admin')

@section('content')
<h1 class="title">داشبورد مدیریت</h1>
<div class="grid desktop-grid-4" style="margin-top:16px;">
    <x-stat-card label="کاربران" :value="$usersCount" hint="اعضای ثبت‌شده" />
    <x-stat-card label="پیش‌بینی‌های فعال" :value="$predictionsCount" hint="توکنی" />
    <x-stat-card label="بازی‌های زنده" :value="$activeMatchesCount" hint="در جریان" />
    <x-stat-card label="تعهد توکنی" :value="number_format($totalCollected)" hint="توکن" />
    <x-stat-card label="کارمزد درگاه" :value="number_format($totalGatewayFee)" hint="حذف‌شده" />
    <x-stat-card label="تسویه‌های باز" :value="$pendingSettlements" hint="دوره" />
</div>
<div class="grid desktop-grid-2" style="margin-top:16px;">
    <x-ui.card>
        <strong>مدیریت بازی‌ها</strong>
        <p class="muted">نتیجه بازی، وضعیت زنده و رویدادها را از جدول بازی‌ها مدیریت کنید.</p>
        <a class="btn btn-primary" href="{{ route('admin.matches') }}">رفتن به بازی‌ها</a>
    </x-ui.card>
    <x-ui.card>
        <strong>تعهدهای توکنی اخیر</strong>
        <div class="leaderboard-list" style="margin-top:12px;">
            @forelse($recentPayments as $payment)
                <div class="leaderboard-row"><span class="rank-bubble">{{ $loop->iteration }}</span><div><strong>{{ $payment->user->full_name }}</strong><div class="muted small">{{ $payment->status }}</div></div><strong>{{ number_format($payment->amount) }} توکن</strong></div>
            @empty
                <p class="muted">تعهدی ثبت نشده است.</p>
            @endforelse
        </div>
    </x-ui.card>
</div>
@endsection
