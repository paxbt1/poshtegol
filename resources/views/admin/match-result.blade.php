@extends('layouts.admin')

@php
    $home = $match->homeTeam?->name_fa ?? $match->bracket_slot_home;
    $away = $match->awayTeam?->name_fa ?? $match->bracket_slot_away;
@endphp

@section('content')
<h1 class="title">مدیریت نتیجه بازی</h1>
<p class="muted">{{ $home }} - {{ $away }}</p>
<div class="grid desktop-grid-2" style="margin-top:16px;">
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">نتیجه و وضعیت</h2>
        <form data-ajax method="POST" action="{{ route('admin.matches.result', $match) }}">
            @csrf
            <div class="score-grid">
                <div class="field"><label>گل {{ $home }}</label><input class="input" name="home_score" value="{{ $match->home_score ?? 0 }}"></div>
                <div class="field"><label>گل {{ $away }}</label><input class="input" name="away_score" value="{{ $match->away_score ?? 0 }}"></div>
            </div>
            @if($match->stage !== 'group' && ! $match->is_placeholder_match)
                <div class="field">
                    <label>تیم صعودکننده / برنده بازی</label>
                    <select class="input" name="qualified_team_id">
                        <option value="">انتخاب نشده</option>
                        <option value="{{ $match->home_team_id }}" @selected($match->qualified_team_id === $match->home_team_id)>{{ $home }}</option>
                        <option value="{{ $match->away_team_id }}" @selected($match->qualified_team_id === $match->away_team_id)>{{ $away }}</option>
                    </select>
                </div>
            @endif
            <div class="field"><label>دقیقه</label><input class="input" name="minute" value="{{ $match->metadata['minute'] ?? '' }}"></div>
            <div class="field">
                <label>وضعیت</label>
                <select class="input" name="status">
                    @foreach(['scheduled'=>'زمان‌بندی‌شده','locked'=>'قفل‌شده','live_first_half'=>'نیمه اول زنده','halftime'=>'بین دو نیمه','live_second_half'=>'نیمه دوم زنده','finished'=>'پایان‌یافته','settled'=>'تسویه‌شده'] as $key => $label)
                        <option value="{{ $key }}" @selected($match->status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary" type="submit" style="margin-top:14px;">ذخیره نتیجه</button>
        </form>
        <form data-ajax method="POST" action="{{ route('admin.matches.calculate-score', $match) }}" style="margin-top:12px;">
            @csrf
            <button class="btn btn-soft" type="submit">محاسبه امتیازها</button>
        </form>
    </x-ui.card>
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">ثبت رویداد</h2>
        <form data-ajax method="POST" action="{{ route('admin.matches.events', $match) }}">
            @csrf
            <div class="field"><label>دقیقه</label><input class="input" name="minute"></div>
            <div class="field"><label>نوع</label><select class="input" name="type"><option value="goal">گل</option><option value="yellow_card">کارت زرد</option><option value="red_card">کارت قرمز</option><option value="halftime">پایان نیمه اول</option><option value="second_half_start">شروع نیمه دوم</option><option value="fulltime">پایان بازی</option><option value="manual_note">یادداشت</option></select></div>
            <div class="field"><label>تیم</label><select class="input" name="team_id"><option value="">بدون تیم</option><option value="{{ $match->home_team_id }}">{{ $home }}</option><option value="{{ $match->away_team_id }}">{{ $away }}</option></select></div>
            <div class="field"><label>عنوان</label><input class="input" name="title"></div>
            <div class="field"><label>توضیح</label><input class="input" name="description"></div>
            <button class="btn btn-primary" type="submit" style="margin-top:14px;">ثبت رویداد</button>
        </form>
    </x-ui.card>
</div>
<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">پیش‌بینی‌های تحت تأثیر</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>کاربر</th><th>نتیجه دقیق</th><th>امتیاز</th><th>وضعیت</th></tr></thead>
            <tbody>
                @foreach($match->predictionEntries as $entry)
                    <tr><td>{{ $entry->user->full_name }}</td><td>{{ $entry->exact_home_score }} - {{ $entry->exact_away_score }}</td><td>{{ $entry->result?->total_points ?? 0 }}</td><td>{{ $entry->payment_status }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
