@extends('layouts.app')

@section('content')
@php
    $homeName = $match->homeTeam?->name_fa ?? $match->bracket_slot_home ?? 'تیم میزبان';
    $awayName = $match->awayTeam?->name_fa ?? $match->bracket_slot_away ?? 'تیم مهمان';
    $homeFlag = $match->homeTeam?->flag_emoji ?? '🏆';
    $awayFlag = $match->awayTeam?->flag_emoji ?? '🏆';
    $homeCrest = $match->homeTeam?->crestDisplayUrl();
    $awayCrest = $match->awayTeam?->crestDisplayUrl();
@endphp
<section class="card hero-card" data-live-room data-status-url="{{ route('api.matches.live-status', $match) }}" style="padding:24px;">
    <div style="position:relative; z-index:1; display:grid; gap:16px; text-align:center;">
        <span class="brand-pill" style="justify-self:center; background:rgba(255,255,255,.12); color:#fff;">پخش زنده وضعیت پیش‌بینی</span>
        <div class="teams">
            <div class="team"><span class="flag team-logo">@if($homeCrest)<img src="{{ $homeCrest }}" alt="{{ $homeName }}">@else{{ $homeFlag }}@endif</span><span>{{ $homeName }}</span></div>
            <div class="versus" style="color:#fff;">VS</div>
            <div class="team"><span class="flag team-logo">@if($awayCrest)<img src="{{ $awayCrest }}" alt="{{ $awayName }}">@else{{ $awayFlag }}@endif</span><span>{{ $awayName }}</span></div>
        </div>
        <strong style="font-size:40px;" data-live-score>{{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}</strong>
        <span data-live-minute>{{ $match->minute ? 'دقیقه '.$match->minute : 'زمان نامشخص' }}</span>
    </div>
</section>

@if($predictionStatus)
<x-ui.card style="margin-top:14px;">
    <h2 class="section-title" style="margin-top:0;">وضعیت پیش‌بینی شما</h2>
    <span data-prediction-live-badge class="badge {{ $predictionStatus['class'] }}">{{ $predictionStatus['label'] }}</span>
    <p class="muted" style="line-height:1.9;">{{ $predictionStatus['description'] }}</p>
</x-ui.card>
@endif

<div class="grid desktop-grid-2" style="margin-top:14px;">
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">اتفاقات بازی</h2>
        <div data-live-events class="leaderboard-list">
            @forelse($match->events as $event)
                <div class="leaderboard-row"><span class="rank-bubble">{{ $event->minute ?? '-' }}</span><div><strong>{{ $event->title }}</strong><div class="muted small">{{ $event->description ?? $event->team?->name_fa }}</div></div></div>
            @empty
                <p class="muted">هنوز اتفاقی ثبت نشده است.</p>
            @endforelse
        </div>
    </x-ui.card>
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">رتبه‌های همین دوره</h2>
        <div class="leaderboard-list">
            @foreach($ranking as $row)
                <div class="leaderboard-row"><span class="rank-bubble">{{ $row->rank }}</span><div><strong>{{ $row->user->full_name }}</strong><div class="muted small">{{ $row->total_entries }} پیش‌بینی</div></div><strong>{{ $row->total_points }}</strong></div>
            @endforeach
        </div>
    </x-ui.card>
</div>
@endsection
