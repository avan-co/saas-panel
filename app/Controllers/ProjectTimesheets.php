<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasProjectNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\PayrollEmployeeModel;
use App\Models\ProjectModel;
use App\Models\ProjectTaskModel;

class ProjectTimesheets extends BaseController
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

        $employees = service('tenantContext')->hasModule('payroll')
            ? model(PayrollEmployeeModel::class)->where('tenant_id', $tenantId)->where('status', 'active')->findAll()
            : [];

        return $this->render('projects/timesheets', [
            'title'           => lang('Projects.timesheets') . ' — ' . $project['name'],
            'project'         => $project,
            'timesheets'      => model(\App\Models\TimesheetModel::class)->getForProject($tenantId, $projectId),
            'employees'       => $employees,
            'tasks'           => model(ProjectTaskModel::class)->getForProject($tenantId, $projectId),
            'canEdit'         => $this->requirePermission('projects.tasks'),
            'projectNav'      => 'timesheets',
            'projectNavItems' => $this->projectNavItems($projectId),
            'breadcrumbs'     => $this->projectBreadcrumbs($project, lang('Projects.timesheets')),
        ]);
    }

    public function store(int $projectId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];

        if (model(ProjectModel::class)->findForTenant($projectId, $tenantId) === null) {
            return redirect()->to('/module/projects')->with('error', lang('Projects.not_found'));
        }

        helper('date');

        $rules = [
            'employee_id' => 'required|integer',
            'work_date'   => 'required',
            'hours'       => 'permit_empty|decimal|greater_than[0]',
            'start_time'  => 'permit_empty',
            'end_time'    => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $workDate = parse_jalali_input((string) $this->request->getPost('work_date'))
            ?? (string) $this->request->getPost('work_date');

        try {
            service('timesheet')->record($tenantId, [
                'employee_id' => (int) $this->request->getPost('employee_id'),
                'project_id'  => $projectId,
                'task_id'     => $this->request->getPost('task_id') ? (int) $this->request->getPost('task_id') : null,
                'work_date'   => $workDate,
                'start_time'  => $this->request->getPost('start_time') ?: null,
                'end_time'    => $this->request->getPost('end_time') ?: null,
                'hours'       => $this->request->getPost('hours') ? (float) $this->request->getPost('hours') : null,
                'note'        => (string) $this->request->getPost('note'),
            ], $tenant);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to('/module/projects/' . $projectId . '/timesheets')->with('success', lang('Projects.timesheet_saved'));
    }
}
