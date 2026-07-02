<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksProjectAccess;
use App\Controllers\Concerns\HasProjectNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\ProjectModel;
use App\Models\ProjectMilestoneModel;
use App\Models\ProjectTaskModel;

class ProjectGantt extends BaseController
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

        return $this->render('projects/gantt', [
            'title'           => lang('Projects.gantt') . ' — ' . $project['name'],
            'project'         => $project,
            'tasks'           => model(ProjectTaskModel::class)->forGantt($tenantId, $projectId),
            'milestones'      => model(ProjectMilestoneModel::class)->forProject($tenantId, $projectId),
            'projectNav'      => 'gantt',
            'projectNavItems' => $this->projectNavItems($projectId),
            'breadcrumbs'     => $this->projectBreadcrumbs($project, lang('Projects.gantt')),
        ]);
    }
}
