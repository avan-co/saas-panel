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
            return redirect()->to('/dashboard');
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

    protected function seedIfEmpty(int $tenantId): void
    {
        if (model(InsurancePolicyModel::class)->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $seeder = new \App\Database\Seeds\DemoDataSeeder(config(\Config\Database::class));
        $seeder->seedInsuranceDemo($tenantId, date('Y-m-d H:i:s'));
    }
}
