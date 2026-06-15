@extends('layouts.public')

@section('content')
<section class="pg-page-head"><span>تیم‌ها</span><h1>تیم‌های جام جهانی</h1><p>فهرست تیم‌های ثبت‌شده در سیستم و لوگوی محلی/وب‌سرویسی آن‌ها.</p></section>
<div class="pg-team-grid">
    @forelse($teams as $team)
        <div class="pg-team-card">
            @if($team->crestDisplayUrl())<img src="{{ $team->crestDisplayUrl() }}" alt="{{ $team->name_fa }}">@else<span>{{ $team->flag_emoji ?: '⚽' }}</span>@endif
            <strong>{{ $team->name_fa }}</strong><em>{{ $team->name_en }}</em>
        </div>
    @empty
        <div class="pg-soft-card">تیمی ثبت نشده است.</div>
    @endforelse
</div>
@endsection
