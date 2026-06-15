@extends('layouts.app')

@section('content')
<h1 class="title">رتبه‌بندی و تسویه‌ها</h1>
<div class="filters" style="margin-top:14px;">
    @foreach($periods as $period)
        <span class="chip {{ $activePeriod?->id === $period->id ? 'active' : '' }}">{{ $period->title }}</span>
    @endforeach
</div>

<div class="grid desktop-grid-2" style="margin-top:16px;">
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">جدول خانوادگی</h2>
        <div class="leaderboard-list mobile-only">
            @forelse($rows as $row)
                <div class="leaderboard-row">
                    <span class="rank-bubble">{{ $row->rank ?? '-' }}</span>
                    <div><strong>{{ $row->user->full_name }}</strong><div class="muted small">{{ $row->total_entries }} پیش‌بینی</div></div>
                    <strong>{{ $row->total_points }} امتیاز</strong>
                </div>
            @empty
                <p class="muted">هنوز امتیازی ثبت نشده است.</p>
            @endforelse
        </div>
        <div class="table-wrap desktop-only">
            <table class="table">
                <thead><tr><th>رتبه</th><th>نام</th><th>پیش‌بینی</th><th>امتیاز</th></tr></thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr><td>{{ $row->rank }}</td><td>{{ $row->user->full_name }}</td><td>{{ $row->total_entries }}</td><td>{{ $row->total_points }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
    <div class="grid">
        <x-ui.card>
            <strong>خلاصه من</strong>
            <div class="summary-row"><span>مبلغ شرکت در بازی‌ها</span><strong>{{ number_format($myResult?->total_entry_amount ?? 0) }} تومان</strong></div>
            <div class="summary-row"><span>امتیاز کل</span><strong>{{ $myResult?->total_points ?? 0 }}</strong></div>
            <div class="summary-row"><span>رتبه شما</span><strong>{{ $myResult?->rank ?? '-' }}</strong></div>
            <div class="summary-row"><span>سهم شما از صندوق</span><strong>{{ number_format($myResult?->reward_amount ?? 0) }} تومان</strong></div>
            <div class="summary-row"><span>پاداش دعوت</span><strong>{{ number_format($myResult?->referral_bonus_amount ?? 0) }} تومان</strong></div>
            <div class="summary-row"><span>مبلغ قابل تسویه</span><strong>{{ number_format($myResult?->final_settlement_amount ?? 0) }} تومان</strong></div>
            <div class="summary-row"><span>وضعیت تسویه</span><strong>{{ $myResult?->settlement_status ?? 'در انتظار' }}</strong></div>
        </x-ui.card>
        <x-ui.card><strong>تاریخچه تسویه</strong><p class="muted">تسویه‌ها پس از نهایی‌سازی دوره توسط مدیر نمایش داده می‌شوند.</p></x-ui.card>
    </div>
</div>
@endsection
