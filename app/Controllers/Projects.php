<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasTenantModule;
use App\Models\ProjectModel;

class Projects extends BaseController
{
    use HasTenantModule;

    public function index()
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null) {
            return redirect()->to('/dashboard');
        }

        $tenantId = (int) $tenant['id'];
        $this->seedIfEmpty($tenantId);

        $projectModel = model(ProjectModel::class);

        return $this->render('projects/index', [
            'title'         => lang('Projects.title'),
            'projects'      => $projectModel->getForTenant($tenantId),
            'activeCount'   => $projectModel->countActive($tenantId),
            'totalBudget'   => $projectModel->totalBudget($tenantId),
            'avgProgress'   => $projectModel->averageProgress($tenantId),
            'breadcrumbs'   => $this->moduleBreadcrumbs(lang('Projects.title')),
        ]);
    }

    protected function seedIfEmpty(int $tenantId): void
    {
        if (model(ProjectModel::class)->where('tenant_id', $tenantId)->countAllResults() > 0) {
            return;
        }

        $seeder = new \App\Database\Seeds\DemoDataSeeder(config(\Config\Database::class));
        $seeder->seedProjectsDemo($tenantId, date('Y-m-d H:i:s'));
    }
}
