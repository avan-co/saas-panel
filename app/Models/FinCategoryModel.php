<?php

namespace App\Models;

use CodeIgniter\Model;

class FinCategoryModel extends Model
{
    protected $table         = 'fin_categories';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'type', 'color', 'sort_order'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }
}
