<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectTaskModel extends Model
{
    protected $table         = 'project_tasks';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'tenant_id', 'project_id', 'title', 'description', 'assignee_user_id',
        'status', 'priority', 'due_date', 'start_date', 'labels', 'depends_on_task_id',
        'estimated_hours', 'actual_hours', 'estimated_cost', 'actual_cost', 'sort_order',
    ];
    protected $useTimestamps = true;

    public function getForProject(int $tenantId, int $projectId): array
    {
        return $this->where('tenant_id', $tenantId)->where('project_id', $projectId)
            ->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll();
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }

    public function progressForProject(int $tenantId, int $projectId): int
    {
        $tasks = $this->getForProject($tenantId, $projectId);

        if ($tasks === []) {
            return 0;
        }

        $done = count(array_filter($tasks, static fn ($t) => $t['status'] === 'done'));

        return (int) round(($done / count($tasks)) * 100);
    }

    public function statsForProject(int $tenantId, int $projectId): array
    {
        $tasks = $this->where('tenant_id', $tenantId)->where('project_id', $projectId)->findAll();
        $open  = 0;
        $overdue = 0;
        $today = date('Y-m-d');

        foreach ($tasks as $task) {
            if (! in_array($task['status'], ['done', 'cancelled'], true)) {
                $open++;

                if (! empty($task['due_date']) && $task['due_date'] < $today) {
                    $overdue++;
                }
            }
        }

        return [
            'total'   => count($tasks),
            'open'    => $open,
            'overdue' => $overdue,
            'done'    => count($tasks) - $open,
        ];
    }

    public function forGantt(int $tenantId, int $projectId): array
    {
        return $this->where('tenant_id', $tenantId)->where('project_id', $projectId)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('start_date', 'ASC')
            ->findAll();
    }
}
