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
            return redirect()->to('/dashboard');
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

    protected function seedIfEmpty(int $tenantId): void
    {
        if (model(TaxPeriodModel::class)->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $seeder = new \App\Database\Seeds\DemoDataSeeder(config(\Config\Database::class));
        $seeder->seedTaxDemo($tenantId, date('Y-m-d H:i:s'));
    }
}
