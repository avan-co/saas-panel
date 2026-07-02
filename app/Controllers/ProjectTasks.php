<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksProjectAccess;
use App\Controllers\Concerns\HasProjectNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\ProjectModel;
use App\Models\ProjectTaskChecklistModel;
use App\Models\ProjectTaskCommentModel;
use App\Models\ProjectTaskModel;
use App\Models\TenantMembershipModel;

class ProjectTasks extends BaseController
{
    use HasTenantModule;
    use HasProjectNav;
    use ChecksProjectAccess;

    protected array $kanbanColumns = ['backlog', 'todo', 'doing', 'review', 'testing', 'done'];

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

        $taskModel = model(ProjectTaskModel::class);
        $tasks     = $taskModel->getForProject($tenantId, $projectId);
        $view      = $this->request->getGet('view') === 'list' ? 'list' : 'kanban';
        $columns   = array_fill_keys($this->kanbanColumns, []);

        foreach ($tasks as $task) {
            $status = $task['status'] === 'in_progress' ? 'doing' : $task['status'];
            $status = in_array($status, $this->kanbanColumns, true) ? $status : 'todo';
            $columns[$status][] = $task;
        }

        $allTasks = $taskModel->select('project_tasks.*, users.name AS assignee_name')
            ->join('users', 'users.id = project_tasks.assignee_user_id', 'left')
            ->where('project_tasks.tenant_id', $tenantId)
            ->where('project_tasks.project_id', $projectId)
            ->orderBy('project_tasks.due_date', 'ASC')
            ->findAll();

        return $this->render($view === 'list' ? 'projects/tasks_list' : 'projects/tasks', [
            'title'           => lang('Projects.tasks') . ' — ' . $project['name'],
            'project'         => $project,
            'columns'         => $columns,
            'tasks'           => $allTasks,
            'progress'        => $taskModel->progressForProject($tenantId, $projectId),
            'canEdit'         => $this->requirePermission('projects.tasks'),
            'users'           => model(TenantMembershipModel::class)->getForTenant($tenantId),
            'projectNav'      => 'tasks',
            'projectNavItems' => $this->projectNavItems($projectId),
            'breadcrumbs'     => $this->projectBreadcrumbs($project, lang('Projects.tasks')),
        ]);
    }

    public function show(int $projectId, int $taskId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $project  = model(ProjectModel::class)->findForTenant($projectId, $tenantId);
        $task     = model(ProjectTaskModel::class)->findForTenant($taskId, $tenantId);

        if ($project === null || $task === null || (int) $task['project_id'] !== $projectId) {
            return redirect()->to('/module/projects')->with('error', lang('App.not_found'));
        }

        if (! $this->requireProjectAccess($projectId)) {
            return $this->projectAccessDeniedRedirect();
        }

        $depends = null;

        if (! empty($task['depends_on_task_id'])) {
            $depends = model(ProjectTaskModel::class)->findForTenant((int) $task['depends_on_task_id'], $tenantId);
        }

        return $this->render('projects/task_show', [
            'title'           => $task['title'],
            'project'         => $project,
            'task'            => $task,
            'depends'         => $depends,
            'checklist'       => model(ProjectTaskChecklistModel::class)->forTask($taskId),
            'comments'        => model(ProjectTaskCommentModel::class)->forTask($taskId),
            'allTasks'        => model(ProjectTaskModel::class)->getForProject($tenantId, $projectId),
            'users'           => model(TenantMembershipModel::class)->getForTenant($tenantId),
            'canEdit'         => $this->requirePermission('projects.tasks'),
            'projectNav'      => 'tasks',
            'projectNavItems' => $this->projectNavItems($projectId),
            'breadcrumbs'     => $this->projectBreadcrumbs($project, $task['title']),
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

        if (! $this->requireProjectAccess($projectId)) {
            return $this->projectAccessDeniedRedirect();
        }

        if (! $this->validate($this->taskRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        helper('date');
        $id = (int) model(ProjectTaskModel::class)->insert($this->taskPayload($tenantId, $projectId));

        return redirect()->to('/module/projects/' . $projectId . '/tasks/' . $id)->with('success', lang('Projects.task_saved'));
    }

    public function update(int $projectId, int $taskId)
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

        if (! $this->validate($this->taskRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $taskModel->update($taskId, $this->taskPayload((int) $tenant['id'], $projectId));

        return redirect()->to('/module/projects/' . $projectId . '/tasks/' . $taskId)->with('success', lang('Projects.task_updated'));
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

        if (! in_array($status, array_merge($this->kanbanColumns, ['cancelled']), true)) {
            return redirect()->back()->with('error', lang('App.invalid_request'));
        }

        $taskModel->update($taskId, ['status' => $status]);
        service('project')->onTaskStatusChange((int) $tenant['id'], $projectId, $task, $status);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }

        return redirect()->to('/module/projects/' . $projectId . '/tasks')->with('success', lang('Projects.task_updated'));
    }

    public function addComment(int $projectId, int $taskId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $task = model(ProjectTaskModel::class)->findForTenant($taskId, (int) $tenant['id']);

        if ($task === null || (int) $task['project_id'] !== $projectId) {
            return redirect()->back()->with('error', lang('App.not_found'));
        }

        $body = trim((string) $this->request->getPost('body'));

        if ($body === '') {
            return redirect()->back();
        }

        model(ProjectTaskCommentModel::class)->insert([
            'task_id'   => $taskId,
            'tenant_id' => (int) $tenant['id'],
            'user_id'   => (int) session('user_id'),
            'parent_id' => $this->request->getPost('parent_id') ? (int) $this->request->getPost('parent_id') : null,
            'body'      => $body,
        ]);

        return redirect()->to('/module/projects/' . $projectId . '/tasks/' . $taskId)->with('success', lang('Projects.comment_added'));
    }

    public function addChecklist(int $projectId, int $taskId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $title = trim((string) $this->request->getPost('title'));

        if ($title !== '') {
            model(ProjectTaskChecklistModel::class)->insert([
                'task_id'   => $taskId,
                'tenant_id' => (int) $tenant['id'],
                'title'     => $title,
            ]);
        }

        return redirect()->to('/module/projects/' . $projectId . '/tasks/' . $taskId);
    }

    public function toggleChecklist(int $projectId, int $taskId, int $itemId)
    {
        $tenant = $this->requireModule('projects');

        if ($tenant === null || ! $this->requirePermission('projects.tasks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $item = model(ProjectTaskChecklistModel::class)->find($itemId);

        if ($item !== null && (int) $item['task_id'] === $taskId) {
            model(ProjectTaskChecklistModel::class)->update($itemId, ['is_done' => $item['is_done'] ? 0 : 1]);
        }

        return redirect()->to('/module/projects/' . $projectId . '/tasks/' . $taskId);
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

    protected function taskRules(): array
    {
        return [
            'title'              => 'required|max_length[200]',
            'priority'           => 'permit_empty|in_list[low,medium,high]',
            'status'             => 'permit_empty|in_list[backlog,todo,doing,review,testing,done,cancelled]',
            'due_date'           => 'permit_empty',
            'start_date'         => 'permit_empty',
            'assignee_user_id'   => 'permit_empty|integer',
            'depends_on_task_id' => 'permit_empty|integer',
            'estimated_hours'    => 'permit_empty|decimal',
        ];
    }

    protected function taskPayload(int $tenantId, int $projectId): array
    {
        helper('date');
        $due   = $this->request->getPost('due_date');
        $start = $this->request->getPost('start_date');
        $due   = $due ? (parse_jalali_input((string) $due) ?? (string) $due) : null;
        $start = $start ? (parse_jalali_input((string) $start) ?? (string) $start) : null;

        return [
            'tenant_id'          => $tenantId,
            'project_id'         => $projectId,
            'title'              => (string) $this->request->getPost('title'),
            'description'        => (string) $this->request->getPost('description'),
            'priority'           => (string) ($this->request->getPost('priority') ?: 'medium'),
            'status'             => (string) ($this->request->getPost('status') ?: 'todo'),
            'due_date'           => $due,
            'start_date'         => $start,
            'labels'             => (string) $this->request->getPost('labels'),
            'assignee_user_id'   => $this->request->getPost('assignee_user_id') ? (int) $this->request->getPost('assignee_user_id') : null,
            'depends_on_task_id' => $this->request->getPost('depends_on_task_id') ? (int) $this->request->getPost('depends_on_task_id') : null,
            'estimated_hours'    => (float) ($this->request->getPost('estimated_hours') ?: 0),
        ];
    }
}
