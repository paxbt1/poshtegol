@extends('layouts.app')

@section('content')
<section class="card hero-card" style="padding:22px;">
    <div style="position:relative; z-index:1;">
        <span class="brand-pill" style="background:rgba(255,255,255,.12); color:#fff;">لیگ خصوصی خانواده</span>
        <h1 class="title" style="margin-top:14px;">جام جهانی ۲۰۲۶</h1>
        <p style="color:rgba(255,255,255,.78);">سلام {{ auth()->user()->full_name }}، برنامه بازی‌ها، وضعیت پیش‌بینی‌ها و رتبه خانوادگی اینجاست.</p>
    </div>
</section>

<div class="grid grid-3" style="margin-top:12px;">
    <x-stat-card label="مرحله گروهی" value="آماده" hint="دعوت فعال است" />
    <x-stat-card label="امتیاز فعلی" :value="$activePeriodResult?->total_points ?? 0" hint="دوره جاری" />
    <x-stat-card label="رتبه فعلی" :value="$activePeriodResult?->rank ?? '-'" hint="در خانواده" />
</div>

<div class="dashboard-layout" style="margin-top:14px;">
    <main class="dashboard-main">
        <x-ui.card>
            <strong>خلاصه زنده</strong>
            <div class="summary-row"><span>بازی‌های زنده</span><strong>{{ $liveMatchesCount }}</strong></div>
            <div class="summary-row"><span>سهم احتمالی از صندوق</span><strong>{{ number_format($activePeriodResult?->reward_amount ?? 0) }} تومان</strong></div>
            <div class="summary-row"><span>پاداش دعوت</span><strong>{{ number_format($activePeriodResult?->referral_bonus_amount ?? 0) }} تومان</strong></div>
        </x-ui.card>

        <h2 class="section-title">بازی‌های امروز</h2>
        <div class="grid">
            @forelse($todayMatches as $match)
                <x-match-card :match="$match" />
                @if(in_array($match->status, ['live_first_half', 'halftime', 'live_second_half'], true))
                    <a class="btn btn-soft" href="{{ route('live.show', $match) }}">ورود به اتاق زنده</a>
                @endif
            @empty
                <x-ui.card><p class="muted">برای امروز بازی‌ای ثبت نشده است. نزدیک‌ترین بازی‌ها را در بخش بازی‌ها ببینید.</p></x-ui.card>
            @endforelse
        </div>

        <div class="grid desktop-grid-3" style="margin-top:16px;">
            <x-ui.card><strong>وضعیت من</strong><p class="muted">پروفایل شما فعال است و می‌توانید تا یک ساعت قبل از شروع هر بازی پیش‌بینی ثبت کنید.</p></x-ui.card>
            <x-ui.card><strong>پیش‌بینی‌های من</strong><p class="muted">{{ $paidPredictions->count() }} پیش‌بینی پرداخت‌شده و قفل‌شده دارید.</p></x-ui.card>
            <x-ui.card><strong>رتبه‌بندی خانوادگی</strong><p class="muted">جدول امتیازها پس از پایان بازی‌ها و محاسبه نتایج به‌روزرسانی می‌شود.</p></x-ui.card>
        </div>

        <x-ui.card style="margin-top:16px;">
            <strong>لینک دعوت من</strong>
            <p class="muted">این لینک تا پایان مرحله گروهی برای پاداش دعوت فعال است.</p>
            <div class="summary-row"><span>اعضای ثبت‌نام‌شده با لینک شما</span><strong>{{ $invitedCount }}</strong></div>
            <button class="btn btn-soft" data-copy="{{ route('join', auth()->user()->invite_code) }}">کپی لینک دعوت</button>
        </x-ui.card>

        <h2 class="section-title">پیش‌بینی‌های پرداخت‌شده</h2>
        <div class="grid">
            @forelse($paidPredictions as $entry)
                @php
                    $home = $entry->match->homeTeam?->name_fa ?? $entry->match->bracket_slot_home;
                    $away = $entry->match->awayTeam?->name_fa ?? $entry->match->bracket_slot_away;
                @endphp
                <x-ui.card>
                    <strong>{{ $home }} - {{ $away }}</strong>
                    <p class="muted">نتیجه دقیق: {{ $entry->exact_home_score }} - {{ $entry->exact_away_score }} / مبلغ ثبت‌شده: {{ number_format($entry->entry_amount) }} تومان</p>
                </x-ui.card>
            @empty
                <x-ui.card><p class="muted">هنوز پیش‌بینی پرداخت‌شده‌ای ندارید.</p></x-ui.card>
            @endforelse
        </div>
    </main>


</div>
@endsection
