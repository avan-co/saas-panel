<?php

use App\Libraries\JalaliDate;

if (! function_exists('jalali_date')) {
    function jalali_date(?string $gregorian, string $format = 'Y/m/d'): string
    {
        if ($gregorian === null || $gregorian === '') {
            return '—';
        }

        return JalaliDate::toJalali($gregorian, $format);
    }
}

if (! function_exists('parse_jalali_input')) {
    function parse_jalali_input(?string $input): ?string
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        if (str_contains($input, '-') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
            return $input;
        }

        return JalaliDate::toGregorian($input);
    }
}

if (! function_exists('today_for_input')) {
    function today_for_input(string $locale = 'fa'): string
    {
        if ($locale === 'fa') {
            return JalaliDate::todayJalali();
        }

        return date('Y-m-d');
    }
}
