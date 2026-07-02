<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksProjectAccess;
use App\Controllers\Concerns\HasProjectNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\ProjectModel;

class ProjectReports extends BaseController
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

        $report = service('project')->reportSummary($tenantId, $projectId);
        $dash   = service('project')->dashboard($tenantId, $projectId);

        return $this->render('projects/reports', [
            'title'           => lang('Projects.reports') . ' — ' . $project['name'],
            'project'         => $project,
            'report'          => $report,
            'prediction'      => $dash['prediction'],
            'projectNav'      => 'reports',
            'projectNavItems' => $this->projectNavItems($projectId),
            'breadcrumbs'     => $this->projectBreadcrumbs($project, lang('Projects.reports')),
        ]);
    }
}
