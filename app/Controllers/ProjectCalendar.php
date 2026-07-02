<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasProjectNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\ProjectModel;

class ProjectCalendar extends BaseController
{
    use HasTenantModule;
    use HasProjectNav;
    use ChecksPermission;

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

        return $this->render('projects/calendar', [
            'title'           => lang('Projects.calendar') . ' — ' . $project['name'],
            'project'         => $project,
            'events'          => service('project')->calendarEvents($tenantId, $projectId),
            'projectNav'      => 'calendar',
            'projectNavItems' => $this->projectNavItems($projectId),
            'breadcrumbs'     => $this->projectBreadcrumbs($project, lang('Projects.calendar')),
        ]);
    }
}
