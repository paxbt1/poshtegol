<?php

namespace App\Services;

use App\Models\AppSetting;

class PaymentAmountService
{
    public function gatewayFee(int $entryAmount): int
    {
        return (int) round($entryAmount * $this->gatewayFeePercent() / 100);
    }

    public function payable(int $entryAmount): int
    {
        return $entryAmount + $this->gatewayFee($entryAmount);
    }

    public function toGateway(int $tomanAmount): int
    {
        return $tomanAmount * app(PaymentGatewaySettings::class)->amountMultiplier();
    }

    public function gatewayFeePercent(): float
    {
        return (float) (AppSetting::where('key', 'gateway_fee_percent')->value('value') ?? env('GATEWAY_FEE_PERCENT', 10));
    }

    public function format(int $amount): string
    {
        return number_format($amount).' تومان';
    }
}
