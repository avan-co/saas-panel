<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinScenarioModel;

class FinanceForecast extends BaseController
{
    use HasTenantModule;
    use HasFinanceNav;
    use ChecksPermission;

    public function index()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $forecast = service('forecast')->forecast($tenantId, 6);

        return $this->render('finance/forecast/index', [
            'title'          => lang('Finance.forecast'),
            'moduleNav'      => 'forecast',
            'moduleNavItems' => $this->financeNavItems(),
            'forecast'       => $forecast,
            'scenarios'      => model(FinScenarioModel::class)->where('tenant_id', $tenantId)->orderBy('created_at', 'DESC')->limit(10)->findAll(),
            'currency'       => $tenant['currency'] ?? 'IRR',
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.forecast')),
        ]);
    }

    public function scenario()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $params   = [
            'months'         => (int) ($this->request->getPost('months') ?: 6),
            'hire_cost'      => (float) ($this->request->getPost('hire_cost') ?: 0),
            'hire_monthly'   => (float) ($this->request->getPost('hire_monthly') ?: 0),
            'equipment_cost' => (float) ($this->request->getPost('equipment_cost') ?: 0),
            'loan_in'        => (float) ($this->request->getPost('loan_in') ?: 0),
        ];

        $result = service('forecast')->scenario($tenantId, $params);
        $name   = (string) ($this->request->getPost('name') ?: lang('Finance.scenario_default'));

        model(FinScenarioModel::class)->insert([
            'tenant_id' => $tenantId,
            'name'      => $name,
            'params'    => json_encode($params, JSON_UNESCAPED_UNICODE),
            'result'    => json_encode($result, JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()->to('/module/finance/forecast')->with('success', lang('Finance.scenario_saved'));
    }
}
