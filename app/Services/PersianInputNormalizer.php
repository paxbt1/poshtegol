<?php

namespace App\Services;

class PersianInputNormalizer
{
    public static function digits(?string $value): string
    {
        $value = trim((string) $value);

        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    public static function mobile(?string $value): string
    {
        $mobile = preg_replace('/\D+/', '', self::digits($value));

        if (str_starts_with($mobile, '0098')) {
            $mobile = '0'.substr($mobile, 4);
        } elseif (str_starts_with($mobile, '98')) {
            $mobile = '0'.substr($mobile, 2);
        }

        return $mobile;
    }

    public static function card(?string $value): string
    {
        return preg_replace('/\D+/', '', self::digits($value));
    }
}
