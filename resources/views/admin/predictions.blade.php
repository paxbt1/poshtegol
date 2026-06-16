@extends('layouts.admin')

@section('content')
<h1 class="title">پیش‌بینی‌ها</h1>
<x-ui.card style="margin-top:16px;">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>کاربر</th><th>بازی</th><th>نتیجه نهایی</th><th>نتیجه دقیق</th><th>توکن</th><th>وضعیت</th></tr></thead>
            <tbody>
                @forelse($predictions as $entry)
                    <tr>
                        <td>{{ $entry->user->full_name }}</td>
                        <td>{{ $entry->match?->homeTeam?->name_fa ?? $entry->match?->bracket_slot_home ?? 'تیم میزبان' }} - {{ $entry->match?->awayTeam?->name_fa ?? $entry->match?->bracket_slot_away ?? 'تیم مهمان' }}</td>
                        <td>{{ $entry->full_time_result ?? '-' }}</td>
                        <td>{{ $entry->exact_home_score }} - {{ $entry->exact_away_score }}</td>
                        <td>{{ number_format($entry->entry_amount) }}</td>
                        <td>{{ $entry->prediction_status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">پیش‌بینی‌ای ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
