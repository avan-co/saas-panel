<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectRiskModel extends Model
{
    protected $table         = 'project_risks';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'project_id', 'title', 'probability', 'impact', 'mitigation', 'owner_user_id', 'status'];
    protected $useTimestamps = true;

    public function forProject(int $tenantId, int $projectId): array
    {
        return $this->select('project_risks.*, users.name AS owner_name')
            ->join('users', 'users.id = project_risks.owner_user_id', 'left')
            ->where('project_risks.tenant_id', $tenantId)
            ->where('project_risks.project_id', $projectId)
            ->orderBy('project_risks.id', 'DESC')
            ->findAll();
    }

    public function countOpen(int $tenantId, int $projectId): int
    {
        return $this->where('tenant_id', $tenantId)->where('project_id', $projectId)
            ->where('status', 'open')->countAllResults();
    }
}
