<?php

namespace App\Models;

use CodeIgniter\Model;

class FinCategoryModel extends Model
{
    protected $table         = 'fin_categories';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'type', 'color', 'sort_order', 'is_active'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function getByType(int $tenantId, string $type): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }
}
