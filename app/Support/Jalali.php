<?php

namespace App\Support;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Carbon;

class Jalali
{
    /**
     * Format a Gregorian date as Jalali date with Persian digits.
     * Supported tokens: Y, y, m, n, d, j, H, i, s, l, F, M.
     */
    public static function format(DateTimeInterface|string|null $date, string $format = 'Y/m/d H:i', string $timezone = 'Asia/Tehran'): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = $date instanceof CarbonInterface
            ? $date->copy()->timezone($timezone)
            : Carbon::parse($date)->timezone($timezone);

        [$jy, $jm, $jd] = self::gregorianToJalali((int) $carbon->format('Y'), (int) $carbon->format('m'), (int) $carbon->format('d'));

        $replacements = [
            'Y' => self::pad($jy, 4),
            'y' => substr((string) $jy, -2),
            'm' => self::pad($jm, 2),
            'n' => (string) $jm,
            'd' => self::pad($jd, 2),
            'j' => (string) $jd,
            'H' => $carbon->format('H'),
            'i' => $carbon->format('i'),
            's' => $carbon->format('s'),
            'l' => self::weekDayName((int) $carbon->dayOfWeek),
            'F' => self::monthName($jm),
            'M' => self::monthName($jm),
        ];

        $result = '';
        $escaping = false;
        $chars = preg_split('//u', $format, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($chars as $char) {
            if ($char === '\\') {
                $escaping = true;
                continue;
            }

            if ($escaping) {
                $result .= $char;
                $escaping = false;
                continue;
            }

            $result .= $replacements[$char] ?? $char;
        }

        return self::digits($result);
    }

    public static function digits(int|float|string|null $value): string
    {
        return strtr((string) ($value ?? ''), [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }

    public static function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        $gy -= 1600;
        $gm -= 1;
        $gd -= 1;

        $gDayNo = 365 * $gy + intdiv($gy + 3, 4) - intdiv($gy + 99, 100) + intdiv($gy + 399, 400);

        for ($i = 0; $i < $gm; $i++) {
            $gDayNo += $gDaysInMonth[$i];
        }

        if ($gm > 1 && (($gy + 1600) % 4 === 0 && (($gy + 1600) % 100 !== 0 || ($gy + 1600) % 400 === 0))) {
            $gDayNo++;
        }

        $gDayNo += $gd;
        $jDayNo = $gDayNo - 79;

        $jNp = intdiv($jDayNo, 12053);
        $jDayNo %= 12053;

        $jy = 979 + 33 * $jNp + 4 * intdiv($jDayNo, 1461);
        $jDayNo %= 1461;

        if ($jDayNo >= 366) {
            $jy += intdiv($jDayNo - 1, 365);
            $jDayNo = ($jDayNo - 1) % 365;
        }

        for ($i = 0; $i < 11 && $jDayNo >= $jDaysInMonth[$i]; $i++) {
            $jDayNo -= $jDaysInMonth[$i];
        }

        return [$jy, $i + 1, $jDayNo + 1];
    }

    private static function pad(int $value, int $length): string
    {
        return str_pad((string) $value, $length, '0', STR_PAD_LEFT);
    }

    private static function monthName(int $month): string
    {
        return [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ][$month] ?? '';
    }

    private static function weekDayName(int $dayOfWeek): string
    {
        return [
            0 => 'یکشنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه',
            4 => 'پنجشنبه', 5 => 'جمعه', 6 => 'شنبه',
        ][$dayOfWeek] ?? '';
    }
}
