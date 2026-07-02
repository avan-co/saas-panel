<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantMembershipModel extends Model
{
    protected $table            = 'tenant_memberships';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['tenant_id', 'user_id', 'role'];
    protected $useTimestamps    = true;

    public function userBelongsToTenant(int $userId, int $tenantId): bool
    {
        return $this->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->countAllResults() > 0;
    }
}
