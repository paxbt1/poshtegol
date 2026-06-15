@extends('layouts.public')

@section('content')
<section class="pg-page-head"><span>اسکوربورد</span><h1>نتایج زنده فوتبال</h1><p>بازی‌های زنده، امروز، پایان‌یافته و بازی‌های پیش رو از منابع داده فوتبال.</p></section>
<section class="pg-score-layout">
    <main>
        <div class="pg-score-section"><h2>زنده</h2>@forelse($liveMatches as $match)<x-public.match-row :match="$match" />@empty<p class="pg-muted">در حال حاضر بازی زنده‌ای ثبت نشده است.</p>@endforelse</div>
        <div class="pg-score-section"><h2>بازی‌های امروز</h2>@forelse($todayMatches as $match)<x-public.match-row :match="$match" />@empty<p class="pg-muted">برای امروز بازی‌ای ثبت نشده است.</p>@endforelse</div>
        <div class="pg-score-section"><h2>نتایج اخیر</h2>@forelse($finishedMatches as $match)<x-public.match-row :match="$match" />@empty<p class="pg-muted">نتیجه‌ای برای نمایش نیست.</p>@endforelse</div>
    </main>
    <aside class="pg-sidebar"><x-public.ad-slot name="scores_sidebar" :slots="$adSlots" /><div class="pg-side-card"><h3>بازی‌های بعدی</h3>@foreach($upcomingMatches->take(8) as $match)<x-public.match-row :match="$match" />@endforeach</div></aside>
</section>
@endsection
