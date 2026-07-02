<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\PayrollEmployeeModel;
use App\Models\PayrollRunItemModel;
use App\Models\PayrollRunModel;

class PayrollRuns extends BaseController
{
    use HasTenantModule;
    use ChecksPermission;

    public function index()
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null || ! $this->requirePermission('payroll.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        return $this->render('payroll/runs/index', [
            'title'       => lang('Payroll.payroll_runs'),
            'runs'        => model(PayrollRunModel::class)->recentForTenant((int) $tenant['id'], 20),
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Payroll.title'), site_url('module/payroll'), lang('Payroll.payroll_runs')),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null || ! $this->requirePermission('payroll.runs')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        return $this->render('payroll/runs/form', [
            'title'       => lang('Payroll.new_run'),
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Payroll.title'), site_url('module/payroll'), lang('Payroll.new_run')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null || ! $this->requirePermission('payroll.runs')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $rules = [
            'period_year'  => 'required|integer|greater_than[1390]',
            'period_month' => 'required|integer|greater_than[0]|less_than[13]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantId = (int) $tenant['id'];
        $year     = (int) $this->request->getPost('period_year');
        $month    = (int) $this->request->getPost('period_month');

        $employees = model(PayrollEmployeeModel::class)
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->findAll();

        if ($employees === []) {
            return redirect()->back()->with('error', lang('Payroll.no_employees'));
        }

        $calc      = service('payrollCalc');
        $total     = 0.0;
        $runId     = model(PayrollRunModel::class)->insert([
            'tenant_id'       => $tenantId,
            'period_year'     => $year,
            'period_month'    => $month,
            'total_amount'    => 0,
            'employee_count'  => count($employees),
            'status'          => 'draft',
        ]);

        $itemModel = model(PayrollRunItemModel::class);

        foreach ($employees as $emp) {
            $result = $calc->calculate($emp);
            $itemModel->insert([
                'run_id'             => $runId,
                'tenant_id'          => $tenantId,
                'employee_id'        => $emp['id'],
                'base_salary'        => $result['base_salary'],
                'insurable_salary'   => $result['insurable_salary'],
                'insurance_employee' => $result['insurance_employee'],
                'insurance_employer' => $result['insurance_employer'],
                'tax_amount'         => $result['tax_amount'],
                'net_pay'            => $result['net_pay'],
            ]);
            $total += $result['net_pay'];
        }

        model(PayrollRunModel::class)->update($runId, ['total_amount' => $total]);
        service('audit')->log($tenantId, 'create', 'payroll_run', (int) $runId);

        return redirect()->to('/module/payroll/runs/' . $runId)->with('success', lang('Payroll.run_created'));
    }

    public function show(int $id)
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null || ! $this->requirePermission('payroll.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $run = model(PayrollRunModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($run === null) {
            return redirect()->to('/module/payroll/runs')->with('error', lang('Payroll.not_found'));
        }

        return $this->render('payroll/runs/show', [
            'title'       => lang('Payroll.run_detail'),
            'run'         => $run,
            'items'       => model(PayrollRunItemModel::class)->getForRun($id),
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Payroll.title'), site_url('module/payroll/runs'), lang('Payroll.run_detail')),
        ]);
    }

    public function approve(int $id)
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null || ! $this->requirePermission('payroll.runs')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $model = model(PayrollRunModel::class);
        $run   = $model->findForTenant($id, (int) $tenant['id']);

        if ($run === null || $run['status'] !== 'draft') {
            return redirect()->back()->with('error', lang('App.not_found'));
        }

        try {
            service('erp')->onPayrollApproved((int) $tenant['id'], $id, $tenant);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $model->update($id, ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')]);
        service('audit')->log((int) $tenant['id'], 'approve', 'payroll_run', $id);

        return redirect()->to('/module/payroll/runs/' . $id)->with('success', lang('Payroll.run_approved'));
    }

    public function exportDsk(int $id)
    {
        $tenant = $this->requireModule('payroll');

        if ($tenant === null || ! $this->requirePermission('payroll.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $run = model(PayrollRunModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($run === null) {
            return redirect()->back()->with('error', lang('App.not_found'));
        }

        $items = model(PayrollRunItemModel::class)->getForRun($id);
        $rows  = [];

        foreach ($items as $item) {
            $rows[] = [
                $item['national_id'] ?? '',
                $item['insurance_number'] ?? '',
                $item['name'],
                30,
                $item['insurable_salary'],
                $item['insurance_employee'],
                $item['insurance_employer'],
            ];
        }

        $headers = ['کد ملی', 'شماره بیمه', 'نام', 'روز', 'مشمول', 'سهم کارگر', 'سهم کارفرا'];
        $csv     = service('export')->csv($headers, $rows);
        $fname   = sprintf('DSK-%d-%02d.csv', $run['period_year'], $run['period_month']);

        return service('export')->downloadResponse($fname, $csv);
    }
}
