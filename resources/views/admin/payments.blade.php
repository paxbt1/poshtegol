@extends('layouts.admin')

@section('content')
<h1 class="title">رسیدهای پرداخت کارت به کارت</h1>
<x-ui.card style="margin-top:16px;">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>کاربر</th>
                    <th>بازی</th>
                    <th>مبلغ</th>
                    <th>کارت مقصد</th>
                    <th>کارت واریزکننده</th>
                    <th>شماره رسید</th>
                    <th>وضعیت</th>
                    <th>زمان ثبت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    @php
                        $payload = $transaction->request_payload ?? [];
                        $match = $transaction->predictionEntry?->match;
                        $home = $match?->homeTeam?->name_fa ?? $match?->bracket_slot_home ?? 'تیم میزبان';
                        $away = $match?->awayTeam?->name_fa ?? $match?->bracket_slot_away ?? 'تیم مهمان';
                        $labels = [
                            'pending_review' => 'در انتظار تایید',
                            'paid' => 'تایید شده',
                            'failed' => 'رد شده',
                            'cancelled' => 'لغو شده',
                        ];
                    @endphp
                    <tr>
                        <td>{{ $transaction->user->full_name }}</td>
                        <td>{{ $home }} - {{ $away }}</td>
                        <td>{{ number_format($transaction->amount) }} تومان</td>
                        <td dir="ltr">{{ $payload['destination_card_number'] ?? '-' }}</td>
                        <td dir="ltr">{{ $payload['payer_card_number'] ?? '-' }}</td>
                        <td dir="ltr">{{ $payload['receipt_number'] ?? $transaction->reference_id ?? '-' }}</td>
                        <td>{{ $labels[$transaction->status] ?? $transaction->status }}</td>
                        <td>{{ \App\Support\Jalali::format($transaction->created_at, 'Y/m/d H:i') }}</td>
                        <td>
                            @if($transaction->status === 'pending_review')
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <form data-ajax method="POST" action="{{ route('admin.payment-transactions.approve', $transaction) }}">
                                        @csrf
                                        <button class="btn btn-primary" type="submit">تایید</button>
                                    </form>
                                    <form data-ajax method="POST" action="{{ route('admin.payment-transactions.reject', $transaction) }}">
                                        @csrf
                                        <button class="btn btn-outline" type="submit">رد</button>
                                    </form>
                                </div>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">رسیدی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
