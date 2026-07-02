<?php

namespace App\Libraries;

use App\Models\DocumentModel;
use App\Models\NotificationModel;
use App\Models\ProjectAutomationRuleModel;
use App\Models\ProjectMemberModel;
use App\Models\ProjectMilestoneModel;
use App\Models\ProjectModel;
use App\Models\ProjectRiskModel;
use App\Models\ProjectTaskModel;
use App\Models\TimesheetModel;

class ProjectService
{
    public function dashboard(int $tenantId, int $projectId): array
    {
        $project   = model(ProjectModel::class)->findForTenant($projectId, $tenantId);
        $taskStats = model(ProjectTaskModel::class)->statsForProject($tenantId, $projectId);
        $progress  = model(ProjectTaskModel::class)->progressForProject($tenantId, $projectId);
        $budget    = (float) ($project['budget'] ?? 0);
        $spent     = (float) ($project['actual_cost'] ?? 0);
        $daysLeft  = null;

        if (! empty($project['end_date'])) {
            $daysLeft = (int) floor((strtotime((string) $project['end_date']) - time()) / 86400);
        }

        $health = $this->calculateHealth($project, $taskStats, $budget, $spent, $daysLeft);

        if (($project['health_status'] ?? '') !== $health) {
            model(ProjectModel::class)->update($projectId, ['health_status' => $health, 'progress' => $progress]);
            $project['health_status'] = $health;
            $project['progress']      = $progress;
        }

        return [
            'project'      => $project,
            'progress'     => $progress,
            'health'       => $health,
            'task_stats'   => $taskStats,
            'budget_spent' => $spent,
            'budget_left'  => max(0, $budget - $spent),
            'days_left'    => $daysLeft,
            'members'      => model(ProjectMemberModel::class)->forProject($tenantId, $projectId),
            'risks_open'   => model(ProjectRiskModel::class)->countOpen($tenantId, $projectId),
            'milestones'   => model(ProjectMilestoneModel::class)->forProject($tenantId, $projectId),
            'recent_tasks' => model(ProjectTaskModel::class)
                ->where('tenant_id', $tenantId)->where('project_id', $projectId)
                ->orderBy('updated_at', 'DESC')->limit(8)->findAll(),
            'prediction'   => $this->predictCompletion($tenantId, $projectId, $project, $taskStats),
        ];
    }

    public function calculateHealth(array $project, array $taskStats, float $budget, float $spent, ?int $daysLeft): string
    {
        if ($taskStats['overdue'] > 0 || ($daysLeft !== null && $daysLeft < 0)) {
            return 'red';
        }

        if ($budget > 0 && $spent / $budget > 0.85) {
            return 'red';
        }

        if ($taskStats['overdue'] > 0 || ($daysLeft !== null && $daysLeft <= 7) || ($budget > 0 && $spent / $budget > 0.7)) {
            return 'yellow';
        }

        return 'green';
    }

    public function predictCompletion(int $tenantId, int $projectId, array $project, array $taskStats): array
    {
        $total = max(1, $taskStats['total']);
        $done  = $taskStats['done'];
        $rate  = $done / $total;

        if ($rate >= 1) {
            return ['days' => 0, 'message' => lang('Projects.prediction_done')];
        }

        $start = ! empty($project['start_date']) ? strtotime((string) $project['start_date']) : strtotime('-30 days');
        $elapsed = max(1, (time() - $start) / 86400);
        $velocity = $done / $elapsed;
        $remaining = $total - $done;
        $daysNeeded = $velocity > 0 ? (int) ceil($remaining / $velocity) : 30;

        $endDate = ! empty($project['end_date']) ? strtotime((string) $project['end_date']) : null;
        $delay = $endDate ? max(0, $daysNeeded - (int) floor(($endDate - time()) / 86400)) : 0;

        return [
            'days'    => $daysNeeded,
            'delay'   => $delay,
            'message' => $delay > 0
                ? lang('Projects.prediction_delay', ['days' => $delay])
                : lang('Projects.prediction_on_track'),
        ];
    }

    public function workload(int $tenantId): array
    {
        $members = model(\App\Models\TenantMembershipModel::class)
            ->select('tenant_memberships.user_id, users.name')
            ->join('users', 'users.id = tenant_memberships.user_id')
            ->where('tenant_memberships.tenant_id', $tenantId)
            ->findAll();

        $result = [];

        foreach ($members as $member) {
            $userId = (int) $member['user_id'];
            $openTasks = model(ProjectTaskModel::class)
                ->where('tenant_id', $tenantId)
                ->where('assignee_user_id', $userId)
                ->whereNotIn('status', ['done', 'cancelled'])
                ->findAll();

            $estHours = 0.0;

            foreach ($openTasks as $task) {
                $estHours += (float) ($task['estimated_hours'] ?? 0) ?: 2.0;
            }

            $projectCount = model(ProjectMemberModel::class)
                ->where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->countAllResults();

            $capacity = 8.0;
            $loadPct  = min(150, (int) round(($estHours / max(1, $capacity * 5)) * 100));

            $result[] = [
                'user_id'     => $userId,
                'name'        => $member['name'],
                'projects'    => $projectCount,
                'hours_today' => min($estHours, $capacity),
                'hours_week'  => $estHours,
                'open_tasks'  => count($openTasks),
                'capacity'    => $capacity,
                'load_pct'    => $loadPct,
                'overload'    => $loadPct > 100,
            ];
        }

        usort($result, static fn ($a, $b) => $b['load_pct'] <=> $a['load_pct']);

        return $result;
    }

    public function onTaskStatusChange(int $tenantId, int $projectId, array $task, string $newStatus): void
    {
        if ($newStatus !== 'done') {
            return;
        }

        $rules = model(ProjectAutomationRuleModel::class)->activeForProject($tenantId, $projectId, 'task_done');

        foreach ($rules as $rule) {
            $config = json_decode((string) ($rule['config'] ?? '{}'), true) ?: [];

            if ($rule['action_type'] === 'create_task' && ! empty($config['title'])) {
                model(ProjectTaskModel::class)->insert([
                    'tenant_id'  => $tenantId,
                    'project_id' => $projectId,
                    'title'      => (string) $config['title'],
                    'status'     => 'todo',
                    'priority'   => $config['priority'] ?? 'medium',
                ]);
            }

            if ($rule['action_type'] === 'notify' && ! empty($config['user_id'])) {
                model(NotificationModel::class)->notifyUser(
                    (int) $config['user_id'],
                    lang('Projects.automation_notify_title'),
                    lang('Projects.automation_task_done', ['task' => $task['title']]),
                    site_url('module/projects/' . $projectId . '/tasks'),
                    $tenantId,
                    'project',
                );
            }
        }

        if (! empty($task['depends_on_task_id'])) {
            return;
        }

        $dependents = model(ProjectTaskModel::class)
            ->where('tenant_id', $tenantId)
            ->where('depends_on_task_id', (int) $task['id'])
            ->where('status', 'backlog')
            ->findAll();

        foreach ($dependents as $dep) {
            model(ProjectTaskModel::class)->update($dep['id'], ['status' => 'todo']);
        }
    }

    public function checkDeadlines(int $tenantId, int $userId): void
    {
        $today = date('Y-m-d');
        $soon  = date('Y-m-d', strtotime('+2 days'));
        $tasks = model(ProjectTaskModel::class)
            ->where('tenant_id', $tenantId)
            ->where('assignee_user_id', $userId)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->where('due_date <=', $soon)
            ->findAll();

        foreach ($tasks as $task) {
            $isOverdue = $task['due_date'] < $today;
            $key       = 'deadline_' . $task['id'] . '_' . ($isOverdue ? 'overdue' : 'soon');

            if (session($key)) {
                continue;
            }

            model(NotificationModel::class)->notifyUser(
                $userId,
                $isOverdue ? lang('Projects.deadline_overdue') : lang('Projects.deadline_soon'),
                $task['title'],
                site_url('module/projects/' . $task['project_id'] . '/tasks/' . $task['id']),
                $tenantId,
                'deadline',
            );
            session()->set($key, true);
        }
    }

    public function reportSummary(int $tenantId, int $projectId): array
    {
        $taskStats = model(ProjectTaskModel::class)->statsForProject($tenantId, $projectId);
        $timesheets = model(TimesheetModel::class)
            ->selectSum('hours', 'total_hours')
            ->selectSum('labor_cost', 'total_cost')
            ->where('tenant_id', $tenantId)
            ->where('project_id', $projectId)
            ->first();

        $members = model(ProjectMemberModel::class)->forProject($tenantId, $projectId);
        $performance = [];

        foreach ($members as $member) {
            $uid = (int) $member['user_id'];
            $done = model(ProjectTaskModel::class)
                ->where('tenant_id', $tenantId)->where('project_id', $projectId)
                ->where('assignee_user_id', $uid)->where('status', 'done')->countAllResults();
            $assigned = model(ProjectTaskModel::class)
                ->where('tenant_id', $tenantId)->where('project_id', $projectId)
                ->where('assignee_user_id', $uid)->countAllResults();
            $hours = model(TimesheetModel::class)
                ->selectSum('hours', 'total')
                ->where('tenant_id', $tenantId)->where('project_id', $projectId)
                ->where('employee_id', $uid)
                ->first();

            $performance[] = [
                'name'       => $member['name'],
                'done'       => $done,
                'assigned'   => $assigned,
                'completion' => $assigned > 0 ? (int) round(($done / $assigned) * 100) : 0,
                'hours'      => (float) ($hours['total'] ?? 0),
            ];
        }

        return [
            'tasks'       => $taskStats,
            'total_hours' => (float) ($timesheets['total_hours'] ?? 0),
            'labor_cost'  => (float) ($timesheets['total_cost'] ?? 0),
            'performance' => $performance,
        ];
    }

    public function calendarEvents(int $tenantId, int $projectId): array
    {
        $events = [];
        $tasks = model(ProjectTaskModel::class)->where('tenant_id', $tenantId)->where('project_id', $projectId)->findAll();

        foreach ($tasks as $task) {
            if (! empty($task['due_date'])) {
                $events[] = ['date' => $task['due_date'], 'type' => 'deadline', 'title' => $task['title'], 'id' => $task['id']];
            }
        }

        foreach (model(ProjectMilestoneModel::class)->forProject($tenantId, $projectId) as $ms) {
            if (! empty($ms['due_date'])) {
                $events[] = ['date' => $ms['due_date'], 'type' => 'milestone', 'title' => $ms['title'], 'id' => $ms['id']];
            }
        }

        usort($events, static fn ($a, $b) => strcmp($a['date'], $b['date']));

        return $events;
    }

    protected function hoursForUserOnDate(int $tenantId, int $userId, string $date): float
    {
        $row = model(TimesheetModel::class)
            ->selectSum('hours', 'total')
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $userId)
            ->where('work_date', $date)
            ->first();

        return (float) ($row['total'] ?? 0);
    }

    protected function hoursForUserRange(int $tenantId, int $userId, string $from, string $to): float
    {
        $row = model(TimesheetModel::class)
            ->selectSum('hours', 'total')
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $userId)
            ->where('work_date >=', $from)
            ->where('work_date <=', $to)
            ->first();

        return (float) ($row['total'] ?? 0);
    }

    public function seedDefaultAutomation(int $tenantId, int $projectId): void
    {
        $model = model(ProjectAutomationRuleModel::class);

        if ($model->where('tenant_id', $tenantId)->where('project_id', $projectId)->countAllResults() > 0) {
            return;
        }

        $model->insert([
            'tenant_id'    => $tenantId,
            'project_id'   => $projectId,
            'trigger_type' => 'task_done',
            'action_type'  => 'notify',
            'config'       => json_encode(['user_id' => session('user_id')]),
            'is_active'    => 1,
        ]);
    }
}
