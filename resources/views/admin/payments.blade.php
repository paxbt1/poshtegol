@extends('layouts.admin')

@section('content')
<h1 class="title">تعهدهای توکنی</h1>
<p class="muted" style="line-height:1.8;margin-top:8px;">هر ردیف یک پیش‌بینی ثبت‌شده با توکن است. پرداخت بانکی در این مرحله انجام نمی‌شود و تسویه نهایی بعد از پایان جام محاسبه خواهد شد.</p>

<x-ui.card style="margin-top:16px;">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>کاربر</th>
                    <th>بازی</th>
                    <th>توکن شرط</th>
                    <th>وضعیت</th>
                    <th>شناسه داخلی</th>
                    <th>زمان ثبت</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    @php
                        $match = $transaction->predictionEntry?->match;
                        $home = $match?->homeTeam?->name_fa ?? $match?->bracket_slot_home ?? 'تیم میزبان';
                        $away = $match?->awayTeam?->name_fa ?? $match?->bracket_slot_away ?? 'تیم مهمان';
                        $labels = [
                            'paid' => 'ثبت‌شده',
                            'failed' => 'ناموفق',
                            'cancelled' => 'لغوشده',
                            'pending_review' => 'در انتظار بررسی',
                        ];
                    @endphp
                    <tr>
                        <td>{{ $transaction->user->full_name }}</td>
                        <td>{{ $home }} - {{ $away }}</td>
                        <td>{{ number_format($transaction->amount) }} توکن</td>
                        <td>{{ $labels[$transaction->status] ?? $transaction->status }}</td>
                        <td dir="ltr">{{ $transaction->reference_id ?? '-' }}</td>
                        <td>{{ \App\Support\Jalali::format($transaction->created_at, 'Y/m/d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">تعهد توکنی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
