<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectIssueModel extends Model
{
    protected $table         = 'project_issues';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'project_id', 'type', 'title', 'description', 'status'];
    protected $useTimestamps = true;

    public function forProject(int $tenantId, int $projectId): array
    {
        return $this->where('tenant_id', $tenantId)->where('project_id', $projectId)
            ->orderBy('id', 'DESC')->findAll();
    }
}
