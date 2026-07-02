<?php

if (! function_exists('module_url')) {
    function module_url(string $code): string
    {
        return match ($code) {
            'dashboard' => site_url('dashboard'),
            'finance'   => site_url('module/finance'),
            'payroll'   => site_url('module/payroll'),
            'insurance' => site_url('module/insurance'),
            'tax'       => site_url('module/tax'),
            'projects'  => site_url('module/projects'),
            'settings'  => site_url('module/settings'),
            default     => site_url('module/' . $code),
        };
    }
}

if (! function_exists('format_amount')) {
    function format_amount(float $amount): string
    {
        return number_format($amount, 0, '.', ',');
    }
}
