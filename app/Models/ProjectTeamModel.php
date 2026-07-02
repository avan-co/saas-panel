<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectTeamModel extends Model
{
    protected $table         = 'project_teams';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'project_id', 'team_id'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function forProject(int $tenantId, int $projectId): array
    {
        return $this->select('project_teams.*, tenant_teams.name, tenant_teams.description')
            ->join('tenant_teams', 'tenant_teams.id = project_teams.team_id')
            ->where('project_teams.tenant_id', $tenantId)
            ->where('project_teams.project_id', $projectId)
            ->orderBy('tenant_teams.name', 'ASC')
            ->findAll();
    }

    public function syncTeams(int $tenantId, int $projectId, array $teamIds): void
    {
        $this->where('tenant_id', $tenantId)->where('project_id', $projectId)->delete();

        foreach ($teamIds as $teamId) {
            $teamId = (int) $teamId;

            if ($teamId <= 0) {
                continue;
            }

            $this->insert([
                'tenant_id'  => $tenantId,
                'project_id' => $projectId,
                'team_id'    => $teamId,
            ]);
        }
    }

    public function projectIdsForUser(int $userId, int $tenantId): array
    {
        $teamIds = model(TenantTeamMemberModel::class)->teamIdsForUser($userId, $tenantId);

        if ($teamIds === []) {
            return [];
        }

        $rows = $this->select('project_id')
            ->where('tenant_id', $tenantId)
            ->whereIn('team_id', $teamIds)
            ->findAll();

        return array_values(array_unique(array_map(static fn ($r) => (int) $r['project_id'], $rows)));
    }
}
