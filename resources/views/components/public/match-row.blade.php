@props(['match'])
@php
    $home = $match->homeTeam?->name_fa ?? $match->bracket_slot_home ?? 'تیم میزبان';
    $away = $match->awayTeam?->name_fa ?? $match->bracket_slot_away ?? 'تیم مهمان';
    $homeLogo = $match->homeTeam?->crest_url;
    $awayLogo = $match->awayTeam?->crest_url;
    $statusLabels = [
        'scheduled' => 'در انتظار', 'timed' => 'زمان‌بندی‌شده', 'live_first_half' => 'زنده',
        'halftime' => 'بین دو نیمه', 'live_second_half' => 'زنده', 'finished' => 'پایان‌یافته',
        'postponed' => 'تعویق', 'cancelled' => 'لغو',
    ];
@endphp
<div class="pg-match-row">
    <div class="pg-team"><span>@if($homeLogo)<img src="{{ $homeLogo }}" alt="{{ $home }}">@endif</span><strong>{{ $home }}</strong></div>
    <div class="pg-scorebox">
        @if($match->home_score !== null || $match->away_score !== null)
            <b>{{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}</b>
        @else
            <b>{{ $match->starts_at ? \App\Support\Jalali::format($match->starts_at, 'H:i') : '-' }}</b>
        @endif
        <small>{{ $statusLabels[$match->status] ?? $match->status }}</small>
    </div>
    <div class="pg-team away"><span>@if($awayLogo)<img src="{{ $awayLogo }}" alt="{{ $away }}">@endif</span><strong>{{ $away }}</strong></div>
</div>
