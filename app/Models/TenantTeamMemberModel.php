<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantTeamMemberModel extends Model
{
    protected $table         = 'tenant_team_members';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['team_id', 'user_id'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function forTeam(int $teamId): array
    {
        return $this->select('tenant_team_members.*, users.name, users.email')
            ->join('users', 'users.id = tenant_team_members.user_id')
            ->where('tenant_team_members.team_id', $teamId)
            ->orderBy('users.name', 'ASC')
            ->findAll();
    }

    public function syncMembers(int $teamId, array $userIds): void
    {
        $this->where('team_id', $teamId)->delete();

        foreach ($userIds as $userId) {
            $userId = (int) $userId;

            if ($userId <= 0) {
                continue;
            }

            $this->insert(['team_id' => $teamId, 'user_id' => $userId]);
        }
    }

    public function teamIdsForUser(int $userId, int $tenantId): array
    {
        $rows = $this->select('tenant_team_members.team_id')
            ->join('tenant_teams', 'tenant_teams.id = tenant_team_members.team_id')
            ->where('tenant_team_members.user_id', $userId)
            ->where('tenant_teams.tenant_id', $tenantId)
            ->findAll();

        return array_map(static fn ($r) => (int) $r['team_id'], $rows);
    }
}
