<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        $kpis = [
            ['key' => 'revenue', 'value' => '۱۲,۴۵۰,۰۰۰', 'change' => '+12%', 'positive' => true],
            ['key' => 'expense', 'value' => '۸,۲۰۰,۰۰۰', 'change' => '-3%', 'positive' => false],
            ['key' => 'payroll', 'value' => '۳,۱۰۰,۰۰۰', 'change' => '0%', 'positive' => true],
            ['key' => 'tax',     'value' => '۹۵۰,۰۰۰', 'change' => '+5%', 'positive' => false],
        ];

        if (session('locale') === 'en') {
            $kpis = [
                ['key' => 'revenue', 'value' => '12,450,000', 'change' => '+12%', 'positive' => true],
                ['key' => 'expense', 'value' => '8,200,000', 'change' => '-3%', 'positive' => false],
                ['key' => 'payroll', 'value' => '3,100,000', 'change' => '0%', 'positive' => true],
                ['key' => 'tax',     'value' => '950,000', 'change' => '+5%', 'positive' => false],
            ];
        }

        return $this->render('dashboard/index', [
            'title'  => lang('Dashboard.title'),
            'tenant' => $tenant,
            'kpis'   => $kpis,
        ]);
    }
}
