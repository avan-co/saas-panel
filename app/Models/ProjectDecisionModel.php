<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectDecisionModel extends Model
{
    protected $table         = 'project_decisions';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'project_id', 'decision', 'owner_user_id', 'due_date', 'task_id'];
    protected $useTimestamps = true;

    public function forProject(int $tenantId, int $projectId): array
    {
        return $this->select('project_decisions.*, users.name AS owner_name')
            ->join('users', 'users.id = project_decisions.owner_user_id', 'left')
            ->where('project_decisions.tenant_id', $tenantId)
            ->where('project_decisions.project_id', $projectId)
            ->orderBy('project_decisions.id', 'DESC')
            ->findAll();
    }
}
