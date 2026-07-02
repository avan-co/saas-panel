<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasTenantModule;
use App\Models\TaxPeriodModel;

class Tax extends BaseController
{
    use HasTenantModule;

    public function index()
    {
        $tenant = $this->requireModule('tax');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $this->seedIfEmpty($tenantId);

        $taxModel = model(TaxPeriodModel::class);

        return $this->render('tax/index', [
            'title'         => lang('Tax.title'),
            'periods'       => $taxModel->getForTenant($tenantId),
            'pendingAmount' => $taxModel->pendingAmount($tenantId),
            'breadcrumbs'   => $this->moduleBreadcrumbs(lang('Tax.title')),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('tax');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        return $this->render('tax/form', [
            'title'       => lang('Tax.new_period'),
            'period'      => null,
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Tax.title'), site_url('module/tax'), lang('Tax.new_period')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('tax');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        if (! $this->validate($this->periodRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(TaxPeriodModel::class)->insert($this->periodPayload((int) $tenant['id']));

        return redirect()->to('/module/tax')->with('success', lang('Tax.saved'));
    }

    public function edit(int $id)
    {
        $tenant = $this->requireModule('tax');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $period = model(TaxPeriodModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($period === null) {
            return redirect()->to('/module/tax')->with('error', lang('App.not_found'));
        }

        return $this->render('tax/form', [
            'title'       => lang('Tax.edit_period'),
            'period'      => $period,
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Tax.title'), site_url('module/tax'), lang('Tax.edit_period')),
        ]);
    }

    public function update(int $id)
    {
        $tenant = $this->requireModule('tax');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $taxModel = model(TaxPeriodModel::class);
        $period   = $taxModel->findForTenant($id, (int) $tenant['id']);

        if ($period === null) {
            return redirect()->to('/module/tax')->with('error', lang('App.not_found'));
        }

        if (! $this->validate($this->periodRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $taxModel->update($id, $this->periodPayload((int) $tenant['id']));

        return redirect()->to('/module/tax')->with('success', lang('Tax.updated'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('tax');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $taxModel = model(TaxPeriodModel::class);
        $period   = $taxModel->findForTenant($id, (int) $tenant['id']);

        if ($period === null) {
            return redirect()->to('/module/tax')->with('error', lang('App.not_found'));
        }

        $taxModel->delete($id);

        return redirect()->to('/module/tax')->with('success', lang('App.deleted'));
    }

    protected function periodRules(): array
    {
        return [
            'period_year'    => 'required|integer|greater_than[2000]',
            'period_quarter' => 'required|integer|greater_than[0]|less_than[5]',
            'taxable_income' => 'required|decimal|greater_than_equal_to[0]',
            'tax_amount'     => 'required|decimal|greater_than_equal_to[0]',
            'status'         => 'required|in_list[pending,filed,paid]',
            'due_date'       => 'permit_empty|valid_date[Y-m-d]',
        ];
    }

    protected function periodPayload(int $tenantId): array
    {
        $dueDate = $this->request->getPost('due_date');

        return [
            'tenant_id'      => $tenantId,
            'period_year'    => (int) $this->request->getPost('period_year'),
            'period_quarter' => (int) $this->request->getPost('period_quarter'),
            'taxable_income' => (float) $this->request->getPost('taxable_income'),
            'tax_amount'     => (float) $this->request->getPost('tax_amount'),
            'status'         => (string) $this->request->getPost('status'),
            'due_date'       => $dueDate !== '' && $dueDate !== null ? (string) $dueDate : null,
        ];
    }

    protected function seedIfEmpty(int $tenantId): void
    {
        if (model(TaxPeriodModel::class)->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $seeder = new \App\Database\Seeds\DemoDataSeeder(config(\Config\Database::class));
        $seeder->seedTaxDemo($tenantId, date('Y-m-d H:i:s'));
    }
}
