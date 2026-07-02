<?php

namespace App\Libraries;

class JalaliDate
{
    public static function toJalali(string $gregorian, string $format = 'Y/m/d'): string
    {
        if ($gregorian === '' || $gregorian === '0000-00-00') {
            return '';
        }

        $ts = strtotime($gregorian);

        if ($ts === false) {
            return $gregorian;
        }

        [$jy, $jm, $jd] = self::gregorianToJalali((int) date('Y', $ts), (int) date('m', $ts), (int) date('d', $ts));

        $map = [
            'Y' => str_pad((string) $jy, 4, '0', STR_PAD_LEFT),
            'm' => str_pad((string) $jm, 2, '0', STR_PAD_LEFT),
            'd' => str_pad((string) $jd, 2, '0', STR_PAD_LEFT),
        ];

        return strtr($format, $map);
    }

    public static function toGregorian(string $jalali): ?string
    {
        $jalali = self::normalizeDigits(trim($jalali));
        $jalali = str_replace('-', '/', $jalali);

        if (! preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $jalali, $m)) {
            return null;
        }

        [$gy, $gm, $gd] = self::jalaliToGregorian((int) $m[1], (int) $m[2], (int) $m[3]);

        return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
    }

    public static function todayJalali(): string
    {
        return self::toJalali(date('Y-m-d'));
    }

    public static function normalizeDigits(string $value): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return str_replace($persian, range(0, 9), str_replace($arabic, range(0, 9), $value));
    }

  /**
   * @return array{0:int,1:int,2:int}
   */
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
        $jNp    = intdiv($jDayNo, 12053);
        $jDayNo %= 12053;
        $jy     = 979 + 33 * $jNp + 4 * intdiv($jDayNo, 1461);
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

  /**
   * @return array{0:int,1:int,2:int}
   */
    public static function jalaliToGregorian(int $jy, int $jm, int $jd): array
    {
        $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
        $jy -= 979;
        $jm -= 1;
        $jd -= 1;
        $jDayNo = 365 * $jy + intdiv($jy, 33) * 8 + intdiv($jy % 33 + 3, 4);

        for ($i = 0; $i < $jm; $i++) {
            $jDayNo += $jDaysInMonth[$i];
        }

        $jDayNo += $jd;
        $gDayNo = $jDayNo + 79;
        $gy     = 1600 + 400 * intdiv($gDayNo, 146097);
        $gDayNo %= 146097;

        $leap = true;

        if ($gDayNo >= 36525) {
            $gDayNo--;
            $gy += 100 * intdiv($gDayNo, 36524);
            $gDayNo %= 36524;

            if ($gDayNo >= 365) {
                $gDayNo++;
            } else {
                $leap = false;
            }
        }

        $gy += 4 * intdiv($gDayNo, 1461);
        $gDayNo %= 1461;

        if ($gDayNo >= 366) {
            $leap = false;
            $gDayNo--;
            $gy += intdiv($gDayNo, 365);
            $gDayNo %= 365;
        }

        $gDaysInMonth = [31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        for ($gm = 0; $gm < 12 && $gDayNo >= $gDaysInMonth[$gm]; $gm++) {
            $gDayNo -= $gDaysInMonth[$gm];
        }

        return [$gy, $gm + 1, $gDayNo + 1];
    }
}
