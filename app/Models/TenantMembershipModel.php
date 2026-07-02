<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantMembershipModel extends Model
{
    protected $table            = 'tenant_memberships';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['tenant_id', 'user_id', 'role', 'permissions', 'manager_id', 'department', 'person_id'];
    protected $useTimestamps    = true;

    public function userBelongsToTenant(int $userId, int $tenantId): bool
    {
        return $this->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->countAllResults() > 0;
    }

    public function getForTenant(int $tenantId): array
    {
        return $this->select('tenant_memberships.*, users.name, users.email, users.status AS user_status')
            ->join('users', 'users.id = tenant_memberships.user_id')
            ->where('tenant_memberships.tenant_id', $tenantId)
            ->orderBy('tenant_memberships.role', 'ASC')
            ->findAll();
    }

    public function orgTree(int $tenantId): array
    {
        $members = $this->getForTenant($tenantId);
        $byId = [];

        foreach ($members as $m) {
            $m['children'] = [];
            $byId[(int) $m['id']] = $m;
        }

        $roots = [];

        foreach ($byId as $id => &$node) {
            $managerId = (int) ($node['manager_id'] ?? 0);

            if ($managerId > 0 && isset($byId[$managerId])) {
                $byId[$managerId]['children'][] = &$node;
            } else {
                $roots[] = &$node;
            }
        }

        return $roots;
    }
}
