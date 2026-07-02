<?php

namespace App\Models;

use CodeIgniter\Model;

class FinContactModel extends Model
{
    protected $table         = 'fin_contacts';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'person_id', 'name', 'type', 'phone', 'email', 'tax_id', 'address', 'balance', 'note', 'is_active'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }

    public function search(int $tenantId, string $q, int $limit = 20): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->groupStart()
            ->like('name', $q)
            ->orLike('phone', $q)
            ->orLike('email', $q)
            ->groupEnd()
            ->limit($limit)
            ->findAll();
    }
}
