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
            return $this->moduleDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $this->seedIfEmpty($tenantId);

        $projectModel = model(ProjectModel::class);

        return $this->render('projects/index', [
            'title'       => lang('Projects.title'),
            'projects'    => $projectModel->getForTenant($tenantId),
            'activeCount' => $projectModel->countActive($tenantId),
            'totalBudget' => $projectModel->totalBudget($tenantId),
            'avgProgress' => $projectModel->averageProgress($tenantId),
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Projects.title')),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        return $this->render('projects/form', [
            'title'       => lang('Projects.new_project'),
            'project'     => null,
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Projects.title'), site_url('module/projects'), lang('Projects.new_project')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        if (! $this->validate($this->projectRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(ProjectModel::class)->insert($this->projectPayload((int) $tenant['id']));

        return redirect()->to('/module/projects')->with('success', lang('Projects.saved'));
    }

    public function edit(int $id)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $project = model(ProjectModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($project === null) {
            return redirect()->to('/module/projects')->with('error', lang('Projects.not_found'));
        }

        return $this->render('projects/form', [
            'title'       => lang('Projects.edit_project'),
            'project'     => $project,
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Projects.title'), site_url('module/projects'), lang('Projects.edit_project')),
        ]);
    }

    public function update(int $id)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $projectModel = model(ProjectModel::class);
        $project      = $projectModel->findForTenant($id, (int) $tenant['id']);

        if ($project === null) {
            return redirect()->to('/module/projects')->with('error', lang('Projects.not_found'));
        }

        if (! $this->validate($this->projectRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $projectModel->update($id, $this->projectPayload((int) $tenant['id']));

        return redirect()->to('/module/projects')->with('success', lang('Projects.updated'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $projectModel = model(ProjectModel::class);
        $project      = $projectModel->findForTenant($id, (int) $tenant['id']);

        if ($project === null) {
            return redirect()->to('/module/projects')->with('error', lang('Projects.not_found'));
        }

        $projectModel->delete($id);

        return redirect()->to('/module/projects')->with('success', lang('App.deleted'));
    }

    protected function projectRules(): array
    {
        return [
            'name'        => 'required|max_length[160]',
            'code'        => 'required|max_length[40]',
            'client_name' => 'permit_empty|max_length[120]',
            'status'      => 'required|in_list[planning,active,on_hold,completed]',
            'budget'      => 'required|decimal|greater_than_equal_to[0]',
            'progress'    => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
            'start_date'  => 'permit_empty|valid_date[Y-m-d]',
            'end_date'    => 'permit_empty|valid_date[Y-m-d]',
        ];
    }

    protected function projectPayload(int $tenantId): array
    {
        $startDate = $this->request->getPost('start_date');
        $endDate   = $this->request->getPost('end_date');

        return [
            'tenant_id'   => $tenantId,
            'name'        => (string) $this->request->getPost('name'),
            'code'        => (string) $this->request->getPost('code'),
            'client_name' => (string) $this->request->getPost('client_name'),
            'status'      => (string) $this->request->getPost('status'),
            'budget'      => (float) $this->request->getPost('budget'),
            'progress'    => (int) $this->request->getPost('progress'),
            'start_date'  => $startDate !== '' && $startDate !== null ? (string) $startDate : null,
            'end_date'    => $endDate !== '' && $endDate !== null ? (string) $endDate : null,
        ];
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
