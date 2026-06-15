@extends('layouts.app')

@section('content')
@php
    $entry = $transaction->predictionEntry;
    $match = $entry?->match;
    $homeName = $match?->homeTeam?->name_fa ?? $match?->bracket_slot_home ?? 'تیم میزبان';
    $awayName = $match?->awayTeam?->name_fa ?? $match?->bracket_slot_away ?? 'تیم مهمان';
    $resultLabels = [
        'home' => 'برد '.$homeName,
        'draw' => 'مساوی',
        'away' => 'برد '.$awayName,
    ];
@endphp

<x-ui.card>
    @if($transaction->status === 'paid')
        <span class="brand-pill">پرداخت موفق</span>
        <h1 class="title" style="margin-top:14px;">پیش‌بینی شما ثبت شد</h1>
        <p class="muted">پیش‌بینی این بازی قفل شد و دیگر قابل تغییر نیست.</p>
    @elseif($transaction->status === 'needs_review')
        <span class="brand-pill" style="background:rgba(245,158,11,.12); color:#a16207;">نیازمند بررسی</span>
        <h1 class="title" style="margin-top:14px;">پرداخت انجام شد اما وضعیت نیازمند بررسی است</h1>
        <p class="muted">پرداخت انجام شد اما زمان پیش‌بینی این بازی به پایان رسیده است. وضعیت توسط مدیر بررسی می‌شود.</p>
    @else
        <span class="brand-pill" style="background:rgba(239,68,68,.12); color:#b91c1c;">پرداخت ناموفق</span>
        <h1 class="title" style="margin-top:14px;">پرداخت کامل نشد</h1>
        <p class="muted">اگر مبلغی از حساب شما کم شده باشد، وضعیت تراکنش قابل پیگیری است.</p>
    @endif
</x-ui.card>

@if($entry && $match)
<x-ui.card style="margin-top:14px;">
    <h2 class="section-title" style="margin-top:0;">{{ $homeName }} - {{ $awayName }}</h2>
    <div class="grid">
        <div class="summary-row"><span>نتیجه نهایی</span><strong>{{ $resultLabels[$entry->full_time_result] ?? '-' }}</strong></div>
        <div class="summary-row"><span>نتیجه دقیق</span><strong>{{ $entry->exact_home_score }} - {{ $entry->exact_away_score }}</strong></div>
        <div class="summary-row"><span>مبلغ ثبت‌شده برای این بازی</span><strong>{{ number_format($entry->entry_amount) }} تومان</strong></div>
        <div class="summary-row"><span>شماره تراکنش</span><strong>{{ $transaction->transaction_id ?? '-' }}</strong></div>
        <div class="summary-row"><span>شماره پیگیری</span><strong>{{ $transaction->reference_id ?? '-' }}</strong></div>
    </div>
    @if($transaction->status === 'failed' && $entry->payment_status === 'failed')
        <form data-ajax method="POST" action="{{ route('predictions.pay', $entry) }}" style="margin-top:14px;">
            @csrf
            <button class="btn btn-primary w-full" type="submit">تلاش دوباره برای پرداخت</button>
        </form>
    @endif
</x-ui.card>
@endif
@endsection
