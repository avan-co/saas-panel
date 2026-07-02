<?php

namespace App\Controllers;

use App\Models\FinTransactionModel;
use App\Models\PayrollEmployeeModel;
use App\Models\TaxPeriodModel;

class Dashboard extends BaseController
{
    public function index()
    {
        helper('module');

        $tenantContext = service('tenantContext');
        $tenant        = $tenantContext->getTenant();
        $modules       = $tenantContext->getModules();
        $moduleCodes   = array_column($modules, 'code');

        $kpis           = $this->buildKpis($tenant, $moduleCodes);
        $recentActivity = $this->buildRecentActivity($tenant, $moduleCodes);

        return $this->render('dashboard/index', [
            'title'          => lang('Dashboard.title'),
            'tenant'         => $tenant,
            'kpis'           => $kpis,
            'recentActivity' => $recentActivity,
        ]);
    }

    protected function buildKpis(?array $tenant, array $moduleCodes): array
    {
        $kpis = [];

        if ($tenant === null) {
            return [
                ['key' => 'revenue', 'value' => '—', 'hint' => null],
                ['key' => 'expense', 'value' => '—', 'hint' => null],
                ['key' => 'payroll', 'value' => '—', 'hint' => null],
                ['key' => 'tax', 'value' => '—', 'hint' => null],
            ];
        }

        $tenantId = (int) $tenant['id'];

        if (in_array('finance', $moduleCodes, true)) {
            $summary = model(FinTransactionModel::class)->monthSummary($tenantId);
            $kpis[]  = ['key' => 'revenue', 'value' => format_amount($summary['income']), 'hint' => lang('Dashboard.kpi_this_month')];
            $kpis[]  = ['key' => 'expense', 'value' => format_amount($summary['expense']), 'hint' => lang('Dashboard.kpi_this_month')];
        } else {
            $kpis[] = ['key' => 'revenue', 'value' => '—', 'hint' => null];
            $kpis[] = ['key' => 'expense', 'value' => '—', 'hint' => null];
        }

        if (in_array('payroll', $moduleCodes, true)) {
            $total  = model(PayrollEmployeeModel::class)->totalPayroll($tenantId);
            $kpis[] = ['key' => 'payroll', 'value' => format_amount($total), 'hint' => lang('Dashboard.kpi_monthly_payroll')];
        } else {
            $kpis[] = ['key' => 'payroll', 'value' => '—', 'hint' => null];
        }

        if (in_array('tax', $moduleCodes, true)) {
            $pending = model(TaxPeriodModel::class)->pendingAmount($tenantId);
            $kpis[]  = ['key' => 'tax', 'value' => format_amount($pending), 'hint' => lang('Dashboard.kpi_pending_tax')];
        } else {
            $kpis[] = ['key' => 'tax', 'value' => '—', 'hint' => null];
        }

        return $kpis;
    }

    protected function buildRecentActivity(?array $tenant, array $moduleCodes): array
    {
        if ($tenant === null || ! in_array('finance', $moduleCodes, true)) {
            return [];
        }

        return model(FinTransactionModel::class)->recentForTenant((int) $tenant['id'], 6);
    }
}
