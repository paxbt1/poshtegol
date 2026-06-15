@extends('layouts.admin')

@section('content')
<h1 class="title">تسویه {{ $period->title }}</h1>
<div class="grid desktop-grid-3" style="margin-top:16px;">
    <x-stat-card label="ورودی صندوق" :value="number_format($settlement?->total_entry_amount ?? 0)" hint="تومان" />
    <x-stat-card label="پرداختی کاربران" :value="number_format($settlement?->total_paid_amount ?? 0)" hint="تومان" />
    <x-stat-card label="کل کارمزد درگاه" :value="number_format($settlement?->total_gateway_fee_amount ?? 0)" hint="ادمین" />
    <x-stat-card label="کل جوایز" :value="number_format($settlement?->total_reward_amount ?? 0)" hint="تومان" />
    <x-stat-card label="پاداش دعوت" :value="number_format($settlement?->total_referral_bonus ?? 0)" hint="تومان" />
    <x-stat-card label="مانده داخلی" :value="number_format($settlement?->net_admin_amount ?? 0)" hint="تومان" />
</div>
<x-ui.card style="margin-top:16px;">
    <div class="filters">
        <form data-ajax method="POST" action="{{ route('admin.settlements.calculate', $period) }}">@csrf<button class="btn btn-soft" type="submit">محاسبه آزمایشی</button></form>
        <form data-ajax method="POST" action="{{ route('admin.settlements.finalize', $period) }}">@csrf<button class="btn btn-primary" type="submit">تأیید نهایی</button></form>
        <a class="btn btn-outline" href="{{ route('admin.settlements.export', $period) }}">تولید فایل تسویه</a>
        <form data-ajax method="POST" action="{{ route('admin.settlements.mark-paid', $period) }}">@csrf<button class="btn btn-soft" type="submit">ثبت پرداخت تسویه</button></form>
    </div>
    <p class="notice" style="margin-top:12px;">بعد از تأیید نهایی، محاسبه دوباره بدون مداخله مدیریتی مجاز نیست.</p>
</x-ui.card>
<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">جدول رتبه و تسویه</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>رتبه</th><th>کاربر</th><th>امتیاز</th><th>جایزه</th><th>پاداش دعوت</th><th>قابل تسویه</th><th>وضعیت</th></tr></thead>
            <tbody>
                @foreach($rows as $row)
                    <tr><td>{{ $row->rank }}</td><td>{{ $row->user->full_name }}</td><td>{{ $row->total_points }}</td><td>{{ number_format($row->reward_amount) }}</td><td>{{ number_format($row->referral_bonus_amount) }}</td><td>{{ number_format($row->final_settlement_amount) }}</td><td>{{ $row->settlement_status }}</td></tr>
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
