<?php

namespace App\Services;

class PaymentAmountService
{
    public function gatewayFee(int $entryAmount): int
    {
        return 0;
    }

    public function payable(int $entryAmount): int
    {
        return $entryAmount;
    }

    public function toGateway(int $tomanAmount): int
    {
        return $tomanAmount;
    }

    public function gatewayFeePercent(): float
    {
        return 0.0;
    }

    public function format(int $amount): string
    {
        return number_format($amount).' تومان';
    }
}
