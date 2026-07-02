<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectMilestoneModel extends Model
{
    protected $table         = 'project_milestones';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'project_id', 'title', 'due_date', 'status', 'description'];
    protected $useTimestamps = true;

    public function forProject(int $tenantId, int $projectId): array
    {
        return $this->where('tenant_id', $tenantId)->where('project_id', $projectId)
            ->orderBy('due_date', 'ASC')->findAll();
    }
}
