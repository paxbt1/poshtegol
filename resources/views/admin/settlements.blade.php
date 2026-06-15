@extends('layouts.admin')

@section('content')
<h1 class="title">تسویه‌ها</h1>
<div class="grid desktop-grid-2" style="margin-top:16px;">
    @foreach(\App\Models\SettlementPeriod::with('settlement')->orderBy('id')->get() as $period)
        <x-ui.card>
            <strong>{{ $period->title }}</strong>
            <p class="muted">وضعیت: {{ $period->settlement?->status ?? 'پیش‌نویس' }}</p>
            <a class="btn btn-primary" href="{{ route('admin.settlements.show', $period) }}">جزئیات تسویه</a>
        </x-ui.card>
    @endforeach
</div>
@endsection
