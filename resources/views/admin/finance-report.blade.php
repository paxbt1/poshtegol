@extends('layouts.admin')

@section('content')
<h1 class="title">گزارش مالی داخلی</h1>
<div class="grid desktop-grid-3" style="margin-top:16px;">
    <x-stat-card label="کل پرداخت موفق" :value="number_format($totalPaid)" hint="تومان" />
    <x-stat-card label="کل کارمزد درگاه" :value="number_format($gatewayFees)" hint="فقط ادمین" />
    <x-stat-card label="تعداد تسویه‌ها" :value="$settlements->count()" hint="دوره" />
</div>
<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">دفتر مالی داخلی</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>نوع</th><th>جهت</th><th>مبلغ</th><th>توضیح</th><th>زمان</th></tr></thead>
            <tbody>
                @foreach($ledgers as $ledger)
                    <tr><td>{{ $ledger->type }}</td><td>{{ $ledger->direction }}</td><td>{{ number_format($ledger->amount) }}</td><td>{{ $ledger->description }}</td><td>{{ \App\Support\Jalali::format($ledger->created_at, 'Y/m/d H:i') }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
