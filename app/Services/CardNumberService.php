<?php

namespace App\Services;

class CardNumberService
{
    public function normalize(?string $card): string
    {
        return PersianInputNormalizer::card($card);
    }

    public function hash(string $card): string
    {
        $secret = config('services.card_hash_secret') ?: config('app.key');

        return hash_hmac('sha256', $this->normalize($card), (string) $secret);
    }

    public function last4(string $card): string
    {
        return substr($this->normalize($card), -4);
    }

    public function passesLuhn(string $card): bool
    {
        $digits = $this->normalize($card);

        if (! preg_match('/^\d{16}$/', $digits)) {
            return false;
        }

        $sum = 0;
        $alternate = false;

        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $number = (int) $digits[$i];

            if ($alternate) {
                $number *= 2;
                if ($number > 9) {
                    $number -= 9;
                }
            }

            $sum += $number;
            $alternate = ! $alternate;
        }

        return $sum % 10 === 0;
    }
}
