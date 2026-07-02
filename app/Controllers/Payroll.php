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
            return $this->moduleDeniedRedirect();
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

    public function createEmployee()
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        return $this->render('payroll/form', [
            'title'       => lang('Payroll.new_employee'),
            'employee'    => null,
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Payroll.title'), site_url('module/payroll'), lang('Payroll.new_employee')),
        ]);
    }

    public function storeEmployee()
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $rules = $this->employeeRules();

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(PayrollEmployeeModel::class)->insert($this->employeePayload((int) $tenant['id']));

        return redirect()->to('/module/payroll')->with('success', lang('Payroll.saved'));
    }

    public function editEmployee(int $id)
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $employee = model(PayrollEmployeeModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($employee === null) {
            return redirect()->to('/module/payroll')->with('error', lang('Payroll.not_found'));
        }

        return $this->render('payroll/form', [
            'title'       => lang('Payroll.edit_employee'),
            'employee'    => $employee,
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Payroll.title'), site_url('module/payroll'), lang('Payroll.edit_employee')),
        ]);
    }

    public function updateEmployee(int $id)
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $employeeModel = model(PayrollEmployeeModel::class);
        $employee      = $employeeModel->findForTenant($id, (int) $tenant['id']);

        if ($employee === null) {
            return redirect()->to('/module/payroll')->with('error', lang('Payroll.not_found'));
        }

        $rules = $this->employeeRules();

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $employeeModel->update($id, $this->employeePayload((int) $tenant['id']));

        return redirect()->to('/module/payroll')->with('success', lang('Payroll.updated'));
    }

    public function deleteEmployee(int $id)
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $employeeModel = model(PayrollEmployeeModel::class);
        $employee      = $employeeModel->findForTenant($id, (int) $tenant['id']);

        if ($employee === null) {
            return redirect()->to('/module/payroll')->with('error', lang('Payroll.not_found'));
        }

        $employeeModel->delete($id);

        return redirect()->to('/module/payroll')->with('success', lang('App.deleted'));
    }

    protected function employeeRules(): array
    {
        return [
            'name'        => 'required|max_length[120]',
            'job_title'   => 'permit_empty|max_length[120]',
            'base_salary' => 'required|decimal|greater_than[0]',
            'status'      => 'required|in_list[active,inactive]',
            'hired_at'    => 'permit_empty|valid_date[Y-m-d]',
        ];
    }

    protected function employeePayload(int $tenantId): array
    {
        $hiredAt = $this->request->getPost('hired_at');

        return [
            'tenant_id'   => $tenantId,
            'name'        => (string) $this->request->getPost('name'),
            'job_title'   => (string) $this->request->getPost('job_title'),
            'base_salary' => (float) $this->request->getPost('base_salary'),
            'status'      => (string) $this->request->getPost('status'),
            'hired_at'    => $hiredAt !== '' && $hiredAt !== null ? (string) $hiredAt : null,
        ];
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
