@extends('layouts.app')

@section('content')
@php
    $entry = $transaction->predictionEntry;
    $match = $entry?->match;
    $homeName = $match?->homeTeam?->name_fa ?? $match?->bracket_slot_home ?? 'تیم میزبان';
    $awayName = $match?->awayTeam?->name_fa ?? $match?->bracket_slot_away ?? 'تیم مهمان';
    $payload = $transaction->request_payload ?? [];
    $statusLabels = [
        'pending_review' => 'در انتظار تایید',
        'paid' => 'تایید شده',
        'failed' => 'رد شده',
        'cancelled' => 'لغو شده',
    ];
@endphp

<x-ui.card>
    @if($transaction->status === 'paid')
        <span class="brand-pill">پرداخت تایید شد</span>
        <h1 class="title" style="margin-top:14px;">پیش‌بینی شما نهایی شد</h1>
        <p class="muted">رسید پرداخت تایید شده و پیش‌بینی وارد محاسبات مالی و امتیازدهی شده است.</p>
    @elseif($transaction->status === 'pending_review')
        <span class="brand-pill" style="background:rgba(245,158,11,.12); color:#a16207;">در انتظار تایید</span>
        <h1 class="title" style="margin-top:14px;">رسید پرداخت ثبت شد</h1>
        <p class="muted">مدیر رسید را بررسی می‌کند. بعد از تایید، پیش‌بینی شما نهایی می‌شود.</p>
    @else
        <span class="brand-pill" style="background:rgba(239,68,68,.12); color:#b91c1c;">{{ $statusLabels[$transaction->status] ?? $transaction->status }}</span>
        <h1 class="title" style="margin-top:14px;">پرداخت تایید نشد</h1>
        <p class="muted">در صورت نیاز دوباره از صفحه بازی رسید صحیح را ثبت کنید.</p>
    @endif
</x-ui.card>

@if($entry && $match)
<x-ui.card style="margin-top:14px;">
    <h2 class="section-title" style="margin-top:0;">{{ $homeName }} - {{ $awayName }}</h2>
    <div class="grid">
        <div class="summary-row"><span>وضعیت رسید</span><strong>{{ $statusLabels[$transaction->status] ?? $transaction->status }}</strong></div>
        <div class="summary-row"><span>مبلغ</span><strong>{{ number_format($transaction->amount) }} تومان</strong></div>
        <div class="summary-row"><span>کارت مقصد</span><strong dir="ltr">{{ $payload['destination_card_number'] ?? '6221061063729273' }}</strong></div>
        <div class="summary-row"><span>کارت واریزکننده</span><strong dir="ltr">{{ $payload['payer_card_number'] ?? '-' }}</strong></div>
        <div class="summary-row"><span>شماره رسید</span><strong dir="ltr">{{ $payload['receipt_number'] ?? $transaction->reference_id ?? '-' }}</strong></div>
        <div class="summary-row"><span>زمان ثبت</span><strong>{{ \App\Support\Jalali::format($transaction->created_at, 'Y/m/d H:i') }}</strong></div>
    </div>
</x-ui.card>
@endif
@endsection
