@extends('layouts.public')

@section('content')
<section class="pg-page-head"><span>برنامه</span><h1>برنامه بازی‌ها</h1><p>زمان‌بندی بازی‌های جام جهانی و فوتبال جهان با نمایش شمسی.</p></section>
<div class="pg-score-section"><h2>بازی‌های پیش رو</h2>@forelse($upcomingMatches as $match)<x-public.match-row :match="$match" />@empty<p class="pg-muted">فعلاً بازی آینده‌ای ثبت نشده است.</p>@endforelse</div>
<div class="pg-score-section"><h2>بازی‌های پایان‌یافته</h2>@foreach($finishedMatches as $match)<x-public.match-row :match="$match" />@endforeach</div>
@endsection
