@extends('layouts.admin')

@section('content')
<h1 class="title">پرداخت‌ها</h1>
<x-ui.card style="margin-top:16px;">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>کاربر</th><th>بازی</th><th>مبلغ ورود به صندوق</th><th>کارمزد درگاه</th><th>مبلغ پرداختی</th><th>وضعیت</th><th>شماره تراکنش</th><th>شماره پیگیری</th><th>زمان</th></tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->user->full_name }}</td>
                        <td>{{ $transaction->predictionEntry?->match?->homeTeam?->name_fa ?? $transaction->predictionEntry?->match?->bracket_slot_home ?? 'تیم میزبان' }} - {{ $transaction->predictionEntry?->match?->awayTeam?->name_fa ?? $transaction->predictionEntry?->match?->bracket_slot_away ?? 'تیم مهمان' }}</td>
                        <td>{{ number_format($transaction->entry_amount) }}</td>
                        <td>{{ number_format($transaction->gateway_fee_amount) }}</td>
                        <td>{{ number_format($transaction->amount) }}</td>
                        <td>{{ ['pending'=>'در انتظار','paid'=>'موفق','failed'=>'ناموفق','cancelled'=>'لغوشده','needs_review'=>'نیازمند بررسی'][$transaction->status] ?? $transaction->status }}</td>
                        <td>{{ $transaction->transaction_id ?? '-' }}</td>
                        <td>{{ $transaction->reference_id ?? '-' }}</td>
                        <td>{{ \App\Support\Jalali::format($transaction->created_at, 'Y/m/d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9">تراکنشی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
