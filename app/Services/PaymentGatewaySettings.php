<?php

namespace App\Services;

use App\Models\AppSetting;

class PaymentGatewaySettings
{
    public function apply(): void
    {
        config(['payment.default' => 'offline_card']);
    }

    public function amountMultiplier(): int
    {
        return 1;
    }

    public function cardNumber(): string
    {
        return (string) AppSetting::getValue('offline_payment_card_number', '6221061063729273');
    }
}
