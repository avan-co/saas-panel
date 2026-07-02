<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasProjectNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinContactModel;
use App\Models\FinTransactionModel;
use App\Models\ProjectMemberModel;
use App\Models\ProjectModel;
use App\Models\TenantMembershipModel;

class Projects extends BaseController
{
    use HasTenantModule;
    use HasProjectNav;
    use ChecksPermission;

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
            'workload'    => service('project')->workload($tenantId),
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Projects.title')),
        ]);
    }

    public function workload()
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        return $this->render('projects/workload', [
            'title'       => lang('Projects.workload'),
            'workload'    => service('project')->workload((int) $tenant['id']),
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Projects.title'), lang('Projects.workload')),
        ]);
    }

    public function show(int $id)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $project  = model(ProjectModel::class)->findForTenant($id, $tenantId);

        if ($project === null) {
            return redirect()->to('/module/projects')->with('error', lang('Projects.not_found'));
        }

        service('project')->checkDeadlines($tenantId, (int) session('user_id'));
        service('project')->seedDefaultAutomation($tenantId, $id);

        $dash = service('project')->dashboard($tenantId, $id);
        $finance = ['income' => 0, 'expense' => 0, 'profit' => 0];
        $transactions = [];

        if (service('tenantContext')->hasModule('finance')) {
            $finance      = model(ProjectModel::class)->financialSummary($tenantId, $id);
            $transactions = model(FinTransactionModel::class)->forProject($tenantId, $id, 10);
        }

        return $this->render('projects/dashboard', [
            'title'            => $project['name'],
            'project'          => $dash['project'],
            'dash'             => $dash,
            'finance'          => $finance,
            'transactions'     => $transactions,
            'hasFinance'       => service('tenantContext')->hasModule('finance'),
            'projectNav'       => 'dashboard',
            'projectNavItems'  => $this->projectNavItems($id),
            'breadcrumbs'      => $this->projectBreadcrumbs($project),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        return $this->render('projects/form', $this->formData((int) $tenant['id'], null));
    }

    public function store()
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        if (! $this->validate($this->projectRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantId = (int) $tenant['id'];
        $id       = (int) model(ProjectModel::class)->insert($this->projectPayload($tenantId));
        $this->syncMembers($tenantId, $id);

        return redirect()->to('/module/projects/' . $id)->with('success', lang('Projects.saved'));
    }

    public function edit(int $id)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $project = model(ProjectModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($project === null) {
            return redirect()->to('/module/projects')->with('error', lang('Projects.not_found'));
        }

        return $this->render('projects/form', $this->formData((int) $tenant['id'], $project));
    }

    public function update(int $id)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $projectModel = model(ProjectModel::class);
        $project      = $projectModel->findForTenant($id, (int) $tenant['id']);

        if ($project === null) {
            return redirect()->to('/module/projects')->with('error', lang('Projects.not_found'));
        }

        if (! $this->validate($this->projectRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantId = (int) $tenant['id'];
        $projectModel->update($id, $this->projectPayload($tenantId));
        $this->syncMembers($tenantId, $id);

        return redirect()->to('/module/projects/' . $id)->with('success', lang('Projects.updated'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $projectModel = model(ProjectModel::class);
        $project      = $projectModel->findForTenant($id, (int) $tenant['id']);

        if ($project === null) {
            return redirect()->to('/module/projects')->with('error', lang('Projects.not_found'));
        }

        $projectModel->delete($id);

        return redirect()->to('/module/projects')->with('success', lang('App.deleted'));
    }

    protected function formData(int $tenantId, ?array $project): array
    {
        $members = $project ? model(ProjectMemberModel::class)->forProject($tenantId, (int) $project['id']) : [];
        $contacts = service('tenantContext')->hasModule('finance')
            ? model(FinContactModel::class)->getForTenant($tenantId) : [];

        return [
            'title'       => $project ? lang('Projects.edit_project') : lang('Projects.new_project'),
            'project'     => $project,
            'members'     => $members,
            'users'       => model(TenantMembershipModel::class)->getForTenant($tenantId),
            'contacts'    => $contacts,
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Projects.title'), site_url('module/projects'), $project ? lang('Projects.edit_project') : lang('Projects.new_project')),
        ];
    }

    protected function syncMembers(int $tenantId, int $projectId): void
    {
        $userIds = $this->request->getPost('member_user_id') ?: [];
        $roles   = $this->request->getPost('member_role') ?: [];
        model(ProjectMemberModel::class)->syncMembers($tenantId, $projectId, (array) $userIds, (array) $roles);

        $managerId = (int) $this->request->getPost('manager_user_id');

        if ($managerId > 0) {
            model(ProjectModel::class)->update($projectId, ['manager_user_id' => $managerId]);
        }
    }

    protected function projectRules(): array
    {
        return [
            'name'        => 'required|max_length[160]',
            'code'        => 'required|max_length[40]',
            'client_name' => 'permit_empty|max_length[120]',
            'contact_id'  => 'permit_empty|integer',
            'manager_user_id' => 'permit_empty|integer',
            'status'      => 'required|in_list[planning,active,on_hold,completed]',
            'priority'    => 'permit_empty|in_list[low,medium,high,critical]',
            'budget'      => 'required|decimal|greater_than_equal_to[0]',
            'start_date'  => 'permit_empty',
            'end_date'    => 'permit_empty',
        ];
    }

    protected function projectPayload(int $tenantId): array
    {
        helper('date');
        $startDate = $this->request->getPost('start_date');
        $endDate   = $this->request->getPost('end_date');
        $startDate = $startDate ? (parse_jalali_input((string) $startDate) ?? (string) $startDate) : null;
        $endDate   = $endDate ? (parse_jalali_input((string) $endDate) ?? (string) $endDate) : null;

        return [
            'tenant_id'       => $tenantId,
            'name'            => (string) $this->request->getPost('name'),
            'code'            => (string) $this->request->getPost('code'),
            'client_name'     => (string) $this->request->getPost('client_name'),
            'contact_id'      => $this->request->getPost('contact_id') ? (int) $this->request->getPost('contact_id') : null,
            'manager_user_id' => $this->request->getPost('manager_user_id') ? (int) $this->request->getPost('manager_user_id') : null,
            'status'          => (string) $this->request->getPost('status'),
            'priority'        => (string) ($this->request->getPost('priority') ?: 'medium'),
            'description'     => (string) $this->request->getPost('description'),
            'budget'          => (float) $this->request->getPost('budget'),
            'start_date'      => $startDate,
            'end_date'        => $endDate,
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
