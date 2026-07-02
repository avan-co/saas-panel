<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasTenantModule;
use App\Models\PayrollEmployeeModel;
use App\Models\PayrollRunModel;

class Payroll extends BaseController
{
    use HasTenantModule;

    public function index()
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null) {
            return redirect()->to('/dashboard');
        }

        $tenantId = (int) $tenant['id'];
        $this->seedIfEmpty($tenantId);

        $employeeModel = model(PayrollEmployeeModel::class);

        return $this->render('payroll/index', [
            'title'       => lang('Payroll.title'),
            'employees'   => $employeeModel->getForTenant($tenantId),
            'runs'        => model(PayrollRunModel::class)->recentForTenant($tenantId),
            'activeCount' => $employeeModel->countActive($tenantId),
            'totalSalary' => $employeeModel->totalPayroll($tenantId),
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Payroll.title')),
        ]);
    }

    protected function seedIfEmpty(int $tenantId): void
    {
        if (model(PayrollEmployeeModel::class)->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $seeder = new \App\Database\Seeds\DemoDataSeeder(config(\Config\Database::class));
        $seeder->seedPayrollDemo($tenantId, date('Y-m-d H:i:s'));
    }
}
