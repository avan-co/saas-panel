<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\ProjectModel;
use App\Models\ProjectTaskModel;

class ProjectTasks extends BaseController
{
    use HasTenantModule;
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

        $taskModel = model(ProjectTaskModel::class);
        $tasks     = $taskModel->getForProject($tenantId, $projectId);
        $columns   = ['todo' => [], 'in_progress' => [], 'done' => []];

        foreach ($tasks as $task) {
            $status = in_array($task['status'], ['todo', 'in_progress', 'done'], true) ? $task['status'] : 'todo';
            $columns[$status][] = $task;
        }

        return $this->render('projects/tasks', [
            'title'       => lang('Projects.tasks') . ' — ' . $project['name'],
            'project'     => $project,
            'columns'     => $columns,
            'progress'    => $taskModel->progressForProject($tenantId, $projectId),
            'canEdit'     => $this->requirePermission('projects.tasks'),
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Projects.title'), site_url('module/projects'), $project['name'], lang('Projects.tasks')),
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

        $rules = [
            'title'    => 'required|max_length[200]',
            'priority' => 'permit_empty|in_list[low,medium,high]',
            'due_date' => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        helper('date');
        $due = $this->request->getPost('due_date');
        $due = $due ? (parse_jalali_input((string) $due) ?? (string) $due) : null;

        model(ProjectTaskModel::class)->insert([
            'tenant_id'  => $tenantId,
            'project_id' => $projectId,
            'title'      => (string) $this->request->getPost('title'),
            'description'=> (string) $this->request->getPost('description'),
            'priority'   => (string) ($this->request->getPost('priority') ?: 'medium'),
            'status'     => 'todo',
            'due_date'   => $due,
        ]);

        return redirect()->to('/module/projects/' . $projectId . '/tasks')->with('success', lang('Projects.task_saved'));
    }

    public function updateStatus(int $projectId, int $taskId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $taskModel = model(ProjectTaskModel::class);
        $task      = $taskModel->findForTenant($taskId, (int) $tenant['id']);

        if ($task === null || (int) $task['project_id'] !== $projectId) {
            return redirect()->back()->with('error', lang('App.not_found'));
        }

        $status = (string) $this->request->getPost('status');

        if (! in_array($status, ['todo', 'in_progress', 'done', 'cancelled'], true)) {
            return redirect()->back()->with('error', lang('App.invalid_request'));
        }

        $taskModel->update($taskId, ['status' => $status]);

        return redirect()->to('/module/projects/' . $projectId . '/tasks')->with('success', lang('Projects.task_updated'));
    }

    public function delete(int $projectId, int $taskId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $taskModel = model(ProjectTaskModel::class);
        $task      = $taskModel->findForTenant($taskId, (int) $tenant['id']);

        if ($task === null || (int) $task['project_id'] !== $projectId) {
            return redirect()->back()->with('error', lang('App.not_found'));
        }

        $taskModel->delete($taskId);

        return redirect()->to('/module/projects/' . $projectId . '/tasks')->with('success', lang('App.deleted'));
    }
}
