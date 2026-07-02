<?php

namespace App\Models;

use CodeIgniter\Model;

class ModuleModel extends Model
{
    protected $table            = 'modules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['code', 'icon', 'sort_order', 'is_active'];
    protected $useTimestamps    = true;

    public function getActive(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function getForTenant(int $tenantId): array
    {
        return $this->db->table('modules m')
            ->select('m.*, tm.enabled, tm.config')
            ->join('tenant_modules tm', 'tm.module_id = m.id')
            ->where('tm.tenant_id', $tenantId)
            ->where('tm.enabled', 1)
            ->where('m.is_active', 1)
            ->orderBy('m.sort_order', 'ASC')
            ->get()
            ->getResultArray();
    }
}
