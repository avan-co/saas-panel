<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksProjectAccess;
use App\Controllers\Concerns\HasProjectNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\ProjectDecisionModel;
use App\Models\ProjectModel;
use App\Models\ProjectTaskModel;
use App\Models\ProjectWikiPageModel;
use App\Models\TenantMembershipModel;

class ProjectWiki extends BaseController
{
    use HasTenantModule;
    use HasProjectNav;
    use ChecksProjectAccess;

    public function index(int $projectId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $project  = model(ProjectModel::class)->findForTenant($projectId, $tenantId);

        if ($project === null) {
            return redirect()->to('/module/projects')->with('error', lang('Projects.not_found'));
        }

        if (! $this->requireProjectAccess($projectId)) {
            return $this->projectAccessDeniedRedirect();
        }

        return $this->render('projects/wiki', [
            'title'           => lang('Projects.wiki') . ' — ' . $project['name'],
            'project'         => $project,
            'pages'           => model(ProjectWikiPageModel::class)->forProject($tenantId, $projectId),
            'decisions'       => model(ProjectDecisionModel::class)->forProject($tenantId, $projectId),
            'users'           => model(TenantMembershipModel::class)->getForTenant($tenantId),
            'canEdit'         => $this->requirePermission('projects.tasks'),
            'projectNav'      => 'wiki',
            'projectNavItems' => $this->projectNavItems($projectId),
            'breadcrumbs'     => $this->projectBreadcrumbs($project, lang('Projects.wiki')),
        ]);
    }

    public function storePage(int $projectId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        model(ProjectWikiPageModel::class)->insert([
            'tenant_id'  => (int) $tenant['id'],
            'project_id' => $projectId,
            'title'      => (string) $this->request->getPost('title'),
            'content'    => (string) $this->request->getPost('content'),
            'created_by' => (int) session('user_id'),
        ]);

        return redirect()->to('/module/projects/' . $projectId . '/wiki')->with('success', lang('Projects.wiki_saved'));
    }

    public function storeDecision(int $projectId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');
        $due = $this->request->getPost('due_date');
        $due = $due ? (parse_jalali_input((string) $due) ?? (string) $due) : null;

        $taskId = null;
        $createTask = $this->request->getPost('create_task');

        if ($createTask) {
            $taskId = (int) model(ProjectTaskModel::class)->insert([
                'tenant_id'        => (int) $tenant['id'],
                'project_id'       => $projectId,
                'title'            => (string) $this->request->getPost('decision'),
                'status'           => 'todo',
                'due_date'         => $due,
                'assignee_user_id' => $this->request->getPost('owner_user_id') ? (int) $this->request->getPost('owner_user_id') : null,
            ]);
        }

        model(ProjectDecisionModel::class)->insert([
            'tenant_id'     => (int) $tenant['id'],
            'project_id'    => $projectId,
            'decision'      => (string) $this->request->getPost('decision'),
            'owner_user_id' => $this->request->getPost('owner_user_id') ? (int) $this->request->getPost('owner_user_id') : null,
            'due_date'      => $due,
            'task_id'       => $taskId,
        ]);

        return redirect()->to('/module/projects/' . $projectId . '/wiki')->with('success', lang('Projects.decision_saved'));
    }
}
