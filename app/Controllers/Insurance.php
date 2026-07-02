<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasTenantModule;
use App\Models\InsurancePolicyModel;

class Insurance extends BaseController
{
    use HasTenantModule;

    public function index()
    {
        $tenant = $this->requireModule('insurance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $this->seedIfEmpty($tenantId);

        $policyModel = model(InsurancePolicyModel::class);

        return $this->render('insurance/index', [
            'title'        => lang('Insurance.title'),
            'policies'     => $policyModel->getForTenant($tenantId),
            'activeCount'  => $policyModel->countActive($tenantId),
            'totalPremium' => $policyModel->totalPremium($tenantId),
            'breadcrumbs'  => $this->moduleBreadcrumbs(lang('Insurance.title')),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('insurance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        return $this->render('insurance/form', [
            'title'       => lang('Insurance.new_policy'),
            'policy'      => null,
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Insurance.title'), site_url('module/insurance'), lang('Insurance.new_policy')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('insurance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        if (! $this->validate($this->policyRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(InsurancePolicyModel::class)->insert($this->policyPayload((int) $tenant['id']));

        return redirect()->to('/module/insurance')->with('success', lang('Insurance.saved'));
    }

    public function edit(int $id)
    {
        $tenant = $this->requireModule('insurance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $policy = model(InsurancePolicyModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($policy === null) {
            return redirect()->to('/module/insurance')->with('error', lang('App.not_found'));
        }

        return $this->render('insurance/form', [
            'title'       => lang('Insurance.edit_policy'),
            'policy'      => $policy,
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Insurance.title'), site_url('module/insurance'), lang('Insurance.edit_policy')),
        ]);
    }

    public function update(int $id)
    {
        $tenant = $this->requireModule('insurance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $policyModel = model(InsurancePolicyModel::class);
        $policy      = $policyModel->findForTenant($id, (int) $tenant['id']);

        if ($policy === null) {
            return redirect()->to('/module/insurance')->with('error', lang('App.not_found'));
        }

        if (! $this->validate($this->policyRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $policyModel->update($id, $this->policyPayload((int) $tenant['id']));

        return redirect()->to('/module/insurance')->with('success', lang('Insurance.updated'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('insurance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $policyModel = model(InsurancePolicyModel::class);
        $policy      = $policyModel->findForTenant($id, (int) $tenant['id']);

        if ($policy === null) {
            return redirect()->to('/module/insurance')->with('error', lang('App.not_found'));
        }

        $policyModel->delete($id);

        return redirect()->to('/module/insurance')->with('success', lang('App.deleted'));
    }

    protected function policyRules(): array
    {
        return [
            'policy_number' => 'required|max_length[80]',
            'provider'      => 'required|max_length[120]',
            'type'          => 'required|in_list[social,health,liability,other]',
            'premium'       => 'required|decimal|greater_than_equal_to[0]',
            'start_date'    => 'required|valid_date[Y-m-d]',
            'end_date'      => 'permit_empty|valid_date[Y-m-d]',
            'status'        => 'required|in_list[active,expired,pending]',
        ];
    }

    protected function policyPayload(int $tenantId): array
    {
        $endDate = $this->request->getPost('end_date');

        return [
            'tenant_id'     => $tenantId,
            'policy_number' => (string) $this->request->getPost('policy_number'),
            'provider'      => (string) $this->request->getPost('provider'),
            'type'          => (string) $this->request->getPost('type'),
            'premium'       => (float) $this->request->getPost('premium'),
            'start_date'    => (string) $this->request->getPost('start_date'),
            'end_date'      => $endDate !== '' && $endDate !== null ? (string) $endDate : null,
            'status'        => (string) $this->request->getPost('status'),
        ];
    }

    protected function seedIfEmpty(int $tenantId): void
    {
        if (model(InsurancePolicyModel::class)->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $seeder = new \App\Database\Seeds\DemoDataSeeder(config(\Config\Database::class));
        $seeder->seedInsuranceDemo($tenantId, date('Y-m-d H:i:s'));
    }
}
