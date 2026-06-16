@props(['state'])
@php
    $labels = [
        'open' => 'باز',
        'closing' => 'در حال بسته‌شدن',
        'locked' => 'بسته‌شده',
        'finished' => 'پایان‌یافته',
    ];
@endphp
<span class="badge badge-{{ $state }}">{{ $labels[$state] ?? 'زمان‌بندی‌شده' }}</span>
