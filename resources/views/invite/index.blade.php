@extends('layouts.app')

@section('content')
<h1 class="title">لینک دعوت شما</h1>

<x-ui.card style="margin-top:14px;">
    <span class="brand-pill">دعوت کاربر</span>
    <p class="muted" style="line-height:1.9;">اگر فردی با لینک شما عضو شود و در پایان مرحله گروهی جایزه بگیرد، پاداش دعوت برای شما محاسبه می‌شود.</p>
    <input class="input" readonly value="{{ $inviteUrl }}">
    <button class="btn btn-primary w-full" data-copy="{{ $inviteUrl }}" style="margin-top:12px;">کپی لینک دعوت</button>
    <p class="muted small">پاداش دعوت فقط برای بردهای مرحله گروهی محاسبه می‌شود.</p>
    <div class="summary-row"><span>پاداش دعوت تخمینی</span><strong>۰ تومان</strong></div>
</x-ui.card>

<x-ui.card style="margin-top:14px;">
    <strong>اعضای دعوت‌شده</strong>
    <div class="table-wrap desktop-only" style="margin-top:12px;">
        <table class="table">
            <thead><tr><th>نام کاربر دعوت‌شده</th><th>تاریخ عضویت</th><th>پیش‌بینی‌های پرداخت‌شده</th><th>وضعیت</th></tr></thead>
            <tbody>
                @forelse($invitedUsers as $user)
                    <tr><td>{{ $user->full_name }}</td><td>{{ \App\Support\Jalali::format($user->created_at, 'Y/m/d') }}</td><td>{{ $user->paid_predictions_count }}</td><td>{{ $user->is_active ? 'فعال' : 'غیرفعال' }}</td></tr>
                @empty
                    <tr><td colspan="4">هنوز عضوی با لینک شما ثبت‌نام نکرده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="leaderboard-list mobile-only" style="margin-top:12px;">
        @forelse($invitedUsers as $user)
            <div class="leaderboard-row">
                <span class="rank-bubble">{{ $loop->iteration }}</span>
                <div><strong>{{ $user->full_name }}</strong><div class="muted small">{{ \App\Support\Jalali::format($user->created_at, 'Y/m/d') }}</div></div>
                <strong>{{ $user->paid_predictions_count }} پیش‌بینی</strong>
            </div>
        @empty
            <p class="muted">هنوز عضوی با لینک شما ثبت‌نام نکرده است.</p>
        @endforelse
    </div>
</x-ui.card>
@endsection
