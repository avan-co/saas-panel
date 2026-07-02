<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectAutomationRuleModel extends Model
{
    protected $table         = 'project_automation_rules';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'project_id', 'trigger_type', 'action_type', 'config', 'is_active'];
    protected $useTimestamps = true;

    public function activeForProject(int $tenantId, int $projectId, string $trigger): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->where('trigger_type', $trigger)
            ->groupStart()
            ->where('project_id', $projectId)
            ->orWhere('project_id', null)
            ->groupEnd()
            ->findAll();
    }
}
