@extends('layouts.app')

@section('content')
<h1 class="title">بازی‌ها</h1>
<p class="muted">برنامه کامل جام جهانی ۲۰۲۶ برای ثبت پیش‌بینی‌های کاپ خانوادگی.</p>

<div class="filters" style="margin-top:14px;">
    <a class="chip {{ request('stage') ? '' : 'active' }}" href="{{ route('matches.index', request()->except('stage')) }}">همه مراحل</a>
    <a class="chip {{ request('stage') === 'group' ? 'active' : '' }}" href="{{ route('matches.index', array_merge(request()->except('stage'), ['stage' => 'group'])) }}">مرحله گروهی</a>
    <a class="chip {{ request('stage') === 'knockout' ? 'active' : '' }}" href="{{ route('matches.index', array_merge(request()->except('stage'), ['stage' => 'knockout'])) }}">حذفی</a>
    <a class="chip {{ request('stage') === 'final' ? 'active' : '' }}" href="{{ route('matches.index', array_merge(request()->except('stage'), ['stage' => 'final'])) }}">فینال</a>
</div>

<div class="filters" style="margin-top:8px;">
    <a class="chip {{ request('time') ? '' : 'active' }}" href="{{ route('matches.index', request()->except('time')) }}">همه زمان‌ها</a>
    <a class="chip {{ request('time') === 'today' ? 'active' : '' }}" href="{{ route('matches.index', array_merge(request()->except('time'), ['time' => 'today'])) }}">امروز</a>
    <a class="chip {{ request('time') === 'tomorrow' ? 'active' : '' }}" href="{{ route('matches.index', array_merge(request()->except('time'), ['time' => 'tomorrow'])) }}">فردا</a>
    <a class="chip {{ request('time') === 'live' ? 'active' : '' }}" href="{{ route('matches.index', array_merge(request()->except('time'), ['time' => 'live'])) }}">در حال برگزاری</a>
    <a class="chip {{ request('time') === 'finished' ? 'active' : '' }}" href="{{ route('matches.index', array_merge(request()->except('time'), ['time' => 'finished'])) }}">پایان‌یافته</a>
</div>

<div class="filters" style="margin-top:8px;">
    <a class="chip {{ request('group') ? '' : 'active' }}" href="{{ route('matches.index', request()->except('group')) }}">همه گروه‌ها</a>
    @foreach(range('A', 'L') as $group)
        <a class="chip {{ request('group') === $group ? 'active' : '' }}" href="{{ route('matches.index', array_merge(request()->except('group'), ['group' => $group])) }}">گروه {{ $group }}</a>
    @endforeach
</div>

<div class="grid desktop-grid-2" style="margin-top:16px;">
    @forelse($matches as $match)
        <x-match-card :match="$match" />
    @empty
        <x-ui.card><p class="muted">بازی‌ای با این فیلتر پیدا نشد.</p></x-ui.card>
    @endforelse
</div>
@endsection
