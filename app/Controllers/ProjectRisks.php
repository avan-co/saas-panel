<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksProjectAccess;
use App\Controllers\Concerns\HasProjectNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\ProjectIssueModel;
use App\Models\ProjectModel;
use App\Models\ProjectRiskModel;
use App\Models\TenantMembershipModel;

class ProjectRisks extends BaseController
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

        return $this->render('projects/risks', [
            'title'           => lang('Projects.risks') . ' — ' . $project['name'],
            'project'         => $project,
            'risks'           => model(ProjectRiskModel::class)->forProject($tenantId, $projectId),
            'issues'          => model(ProjectIssueModel::class)->forProject($tenantId, $projectId),
            'users'           => model(TenantMembershipModel::class)->getForTenant($tenantId),
            'canEdit'         => $this->requirePermission('projects.tasks'),
            'projectNav'      => 'risks',
            'projectNavItems' => $this->projectNavItems($projectId),
            'breadcrumbs'     => $this->projectBreadcrumbs($project, lang('Projects.risks')),
        ]);
    }

    public function storeRisk(int $projectId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        model(ProjectRiskModel::class)->insert([
            'tenant_id'     => (int) $tenant['id'],
            'project_id'    => $projectId,
            'title'         => (string) $this->request->getPost('title'),
            'probability'   => (string) ($this->request->getPost('probability') ?: 'medium'),
            'impact'        => (string) ($this->request->getPost('impact') ?: 'medium'),
            'mitigation'    => (string) $this->request->getPost('mitigation'),
            'owner_user_id' => $this->request->getPost('owner_user_id') ? (int) $this->request->getPost('owner_user_id') : null,
        ]);

        return redirect()->to('/module/projects/' . $projectId . '/risks')->with('success', lang('Projects.risk_saved'));
    }

    public function storeIssue(int $projectId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        model(ProjectIssueModel::class)->insert([
            'tenant_id'   => (int) $tenant['id'],
            'project_id'  => $projectId,
            'type'        => (string) ($this->request->getPost('type') ?: 'internal'),
            'title'       => (string) $this->request->getPost('title'),
            'description' => (string) $this->request->getPost('description'),
        ]);

        return redirect()->to('/module/projects/' . $projectId . '/risks')->with('success', lang('Projects.issue_saved'));
    }
}
