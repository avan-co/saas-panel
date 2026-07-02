<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantTeamModel extends Model
{
    protected $table         = 'tenant_teams';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'description', 'leader_user_id'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)->orderBy('name', 'ASC')->findAll();
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }

    public function withMembers(int $tenantId): array
    {
        $teams = $this->getForTenant($tenantId);
        $memberModel = model(TenantTeamMemberModel::class);

        foreach ($teams as &$team) {
            $team['members'] = $memberModel->forTeam((int) $team['id']);
            $team['member_count'] = count($team['members']);
        }

        return $teams;
    }
}
