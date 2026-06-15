@extends('layouts.admin')

@section('content')
<h1 class="title">مدیریت بازی‌ها</h1>
<x-ui.card style="margin-top:16px;">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>شماره</th><th>بازی</th><th>مرحله</th><th>زمان شمسی</th><th>شمارش معکوس</th><th>وضعیت</th><th>عملیات</th></tr></thead>
            <tbody>
                @foreach($matches as $match)
                    <tr>
                        <td>{{ $match->match_number }}</td>
                        <td>{{ $match->homeTeam?->name_fa ?? $match->bracket_slot_home ?? 'تیم میزبان' }} - {{ $match->awayTeam?->name_fa ?? $match->bracket_slot_away ?? 'تیم مهمان' }}</td>
                        <td>{{ $match->stage_label_fa ?? $match->stage }}</td>
                        <td>{{ \App\Support\Jalali::format($match->starts_at, 'Y/m/d H:i') }}</td>
                        <td><span class="countdown-line" data-countdown data-mode="compact" data-starts-at="{{ $match->starts_at?->timezone('Asia/Tehran')->toIso8601String() }}">در حال محاسبه...</span></td>
                        <td>{{ $match->status }}</td>
                        <td><a class="btn btn-soft" href="{{ route('admin.matches.edit-result', $match) }}">مدیریت نتیجه</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
