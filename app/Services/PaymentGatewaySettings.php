<?php

namespace App\Services;

use App\Models\AppSetting;

class PaymentGatewaySettings
{
    public function apply(): void
    {
        $driver = (string) AppSetting::getValue('payment_driver', config('payment.default', 'zibal'));
        $driver = $driver !== '' ? $driver : 'zibal';

        config(['payment.default' => $driver]);

        if ($driver === 'zibal') {
            $sandbox = filter_var(AppSetting::getValue('zibal_sandbox', env('ZIBAL_SANDBOX', false) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
            $merchantId = $sandbox ? 'zibal' : (string) AppSetting::getValue('zibal_merchant_id', env('ZIBAL_MERCHANT_ID', ''));
            $callbackUrl = (string) AppSetting::getValue('zibal_callback_url', route('payment.callback.zibal'));
            $currency = (string) AppSetting::getValue('payment_currency', config('payment.drivers.zibal.currency', 'R'));

            config([
                'payment.drivers.zibal.merchantId' => $merchantId,
                'payment.drivers.zibal.callbackUrl' => $callbackUrl ?: route('payment.callback.zibal'),
                'payment.drivers.zibal.currency' => in_array($currency, ['R', 'T'], true) ? $currency : 'R',
                'payment.drivers.zibal.description' => (string) AppSetting::getValue('zibal_description', 'پرداخت کاپ خانوادگی'),
            ]);
        }
    }

    public function amountMultiplier(): int
    {
        return max(1, (int) AppSetting::getValue('payment_amount_multiplier', env('PAYMENT_AMOUNT_MULTIPLIER', 10)));
    }
}
