@extends('layouts.admin')

@php
    $statusLabel = function (?string $status) {
        return match (true) {
            str_contains((string) $status, 'debtor') => 'بدهکار',
            str_contains((string) $status, 'creditor') => 'بستانکار',
            str_contains((string) $status, 'balanced') => 'تسویه',
            $status === 'paid' => 'پرداخت‌شده',
            default => $status ?: 'در انتظار',
        };
    };
@endphp

@section('content')
<h1 class="title">تسویه {{ $period->title }}</h1>
<div class="grid desktop-grid-3" style="margin-top:16px;">
    <x-stat-card label="صندوق توکنی" :value="number_format($settlement?->total_entry_amount ?? 0)" hint="توکن" />
    <x-stat-card label="توکن ثبت‌شده کاربران" :value="number_format($settlement?->total_paid_amount ?? 0)" hint="توکن" />
    <x-stat-card label="کل جوایز" :value="number_format($settlement?->total_reward_amount ?? 0)" hint="توکن" />
    <x-stat-card label="پاداش دعوت" :value="number_format($settlement?->total_referral_bonus ?? 0)" hint="توکن" />
    <x-stat-card label="کارمزد درگاه" :value="number_format($settlement?->total_gateway_fee_amount ?? 0)" hint="حذف‌شده" />
    <x-stat-card label="وضعیت دوره" :value="$settlement?->status ?? 'draft'" hint="تسویه" />
</div>
<x-ui.card style="margin-top:16px;">
    <div class="filters">
        <form data-ajax method="POST" action="{{ route('admin.settlements.calculate', $period) }}">@csrf<button class="btn btn-soft" type="submit">محاسبه آزمایشی</button></form>
        <form data-ajax method="POST" action="{{ route('admin.settlements.finalize', $period) }}">@csrf<button class="btn btn-primary" type="submit">تأیید نهایی</button></form>
        <a class="btn btn-outline" href="{{ route('admin.settlements.export', $period) }}">خروجی تسویه</a>
        <form data-ajax method="POST" action="{{ route('admin.settlements.mark-paid', $period) }}">@csrf<button class="btn btn-soft" type="submit">ثبت انجام تسویه</button></form>
    </div>
    <p class="notice" style="margin-top:12px;">عدد قابل تسویه بر اساس خالص توکن محاسبه می‌شود: جایزه و پاداش دعوت منهای توکن‌های شرط‌بسته‌شده. هر ۱ توکن برابر ۱ تومان است و وضعیت «بدهکار» یعنی کاربر باید معادل همان تعداد توکن واریز کند.</p>
</x-ui.card>
<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">جدول رتبه و تسویه</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>رتبه</th><th>کاربر</th><th>امتیاز</th><th>توکن شرط</th><th>جایزه</th><th>پاداش دعوت</th><th>وضعیت</th><th>مقدار تسویه</th></tr></thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row->rank }}</td>
                        <td>{{ $row->user->full_name }}</td>
                        <td>{{ $row->total_points }}</td>
                        <td>{{ number_format($row->total_entry_amount) }}</td>
                        <td>{{ number_format($row->reward_amount) }}</td>
                        <td>{{ number_format($row->referral_bonus_amount) }}</td>
                        <td>{{ $statusLabel($row->settlement_status) }}</td>
                        <td>{{ number_format($row->final_settlement_amount) }} توکن</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-ui.card>
<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">پاداش‌های دعوت</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>دعوت‌کننده</th><th>دعوت‌شده</th><th>پایه جایزه</th><th>درصد</th><th>پاداش</th><th>وضعیت</th></tr></thead>
            <tbody>
                @forelse($commissions as $commission)
                    <tr><td>{{ $commission->inviter->full_name }}</td><td>{{ $commission->referred->full_name }}</td><td>{{ number_format($commission->base_reward_amount) }}</td><td>{{ $commission->commission_rate }}</td><td>{{ number_format($commission->commission_amount) }}</td><td>{{ $commission->status }}</td></tr>
                @empty
                    <tr><td colspan="6">پاداش دعوتی محاسبه نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
