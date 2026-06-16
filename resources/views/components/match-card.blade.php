@props(['match'])

@php
    $homeName = $match->homeTeam?->name_fa ?? $match->bracket_slot_home ?? 'تیم میزبان';
    $awayName = $match->awayTeam?->name_fa ?? $match->bracket_slot_away ?? 'تیم مهمان';
    $homeFlag = $match->homeTeam?->flag_emoji ?? '🏆';
    $awayFlag = $match->awayTeam?->flag_emoji ?? '🏆';
    $homeCrest = $match->homeTeam?->crestDisplayUrl();
    $awayCrest = $match->awayTeam?->crestDisplayUrl();
    $startsIso = $match->starts_at?->timezone('Asia/Tehran')->toIso8601String();
    $canPredict = app(\App\Services\MatchLockService::class)->canPredict($match);
    $isFinished = in_array($match->status, ['finished', 'awarded', 'after_extra_time', 'after_penalties', 'settled'], true);
    $hasScore = $match->home_score !== null && $match->away_score !== null;
@endphp

<x-ui.card class="match-card {{ $canPredict ? '' : 'is-disabled' }}">
    <div style="display:flex; justify-content:space-between; gap:8px; align-items:center;">
        <span class="muted small">{{ $match->stage_label_fa ?? $match->period?->title ?? 'جام جهانی ۲۰۲۶' }} @if($match->group_name) / گروه {{ $match->group_name }} @endif</span>
        <x-status-badge :state="$match->predictionState()" />
    </div>
    <div class="teams">
        <div class="team">
            <span class="flag team-logo">@if($homeCrest)<img src="{{ $homeCrest }}" alt="{{ $homeName }}">@else{{ $homeFlag }}@endif</span>
            <span>{{ $homeName }}</span>
        </div>
        <div class="{{ $isFinished && $hasScore ? 'final-score-pill' : 'versus' }}">
            {{ $isFinished && $hasScore ? $match->home_score.' - '.$match->away_score : 'VS' }}
        </div>
        <div class="team">
            <span class="flag team-logo">@if($awayCrest)<img src="{{ $awayCrest }}" alt="{{ $awayName }}">@else{{ $awayFlag }}@endif</span>
            <span>{{ $awayName }}</span>
        </div>
    </div>
    @if($isFinished && $hasScore)
        <div class="match-result-line">نتیجه نهایی: {{ $homeName }} {{ $match->home_score }} - {{ $match->away_score }} {{ $awayName }}</div>
    @else
        <div class="countdown-box" data-countdown data-starts-at="{{ $startsIso }}"></div>
    @endif
    <div style="display:flex; justify-content:space-between; gap:10px; align-items:center;">
        <span class="muted small">{{ \App\Support\Jalali::format($match->starts_at, 'Y/m/d H:i') }} - {{ $match->city ?? 'محل نامشخص' }}</span>
        @if($canPredict)
            <a class="btn btn-primary" href="{{ route('matches.show', $match) }}">ثبت پیش‌بینی</a>
        @elseif($isFinished)
            <span class="badge badge-finished">پایان‌یافته</span>
        @else
            <span class="badge badge-locked">بسته شده</span>
        @endif
    </div>
</x-ui.card>
