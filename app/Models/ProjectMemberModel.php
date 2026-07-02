<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectMemberModel extends Model
{
    protected $table         = 'project_members';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'project_id', 'user_id', 'role'];
    protected $useTimestamps = true;

    public function forProject(int $tenantId, int $projectId): array
    {
        return $this->select('project_members.*, users.name, users.email')
            ->join('users', 'users.id = project_members.user_id')
            ->where('project_members.tenant_id', $tenantId)
            ->where('project_members.project_id', $projectId)
            ->orderBy('project_members.role', 'ASC')
            ->findAll();
    }

    public function syncMembers(int $tenantId, int $projectId, array $userIds, array $roles): void
    {
        $this->where('tenant_id', $tenantId)->where('project_id', $projectId)->delete();

        foreach ($userIds as $i => $userId) {
            $userId = (int) $userId;

            if ($userId <= 0) {
                continue;
            }

            $this->insert([
                'tenant_id'  => $tenantId,
                'project_id' => $projectId,
                'user_id'    => $userId,
                'role'       => $roles[$i] ?? 'expert',
            ]);
        }
    }
}
