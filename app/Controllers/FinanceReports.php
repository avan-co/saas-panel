<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinTransactionModel;

class FinanceReports extends BaseController
{
    use HasTenantModule;
    use HasFinanceNav;

    public function index()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        helper('date');
        $tenantId = (int) $tenant['id'];
        $txnModel = model(FinTransactionModel::class);

        return $this->render('finance/reports/index', [
            'title'          => lang('Finance.reports'),
            'moduleNav'      => 'reports',
            'moduleNavItems' => $this->financeNavItems(),
            'monthSummary'   => $txnModel->monthSummary($tenantId),
            'cashflow'       => $txnModel->dailyCashflow($tenantId, 30),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.reports')),
        ]);
    }
}
