<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantModuleModel extends Model
{
    protected $table            = 'tenant_modules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['tenant_id', 'module_id', 'enabled', 'config'];
    protected $useTimestamps    = true;

    public function setModulesForTenant(int $tenantId, array $moduleIds): void
    {
        $this->where('tenant_id', $tenantId)->delete();

        foreach ($moduleIds as $moduleId) {
            $this->insert([
                'tenant_id' => $tenantId,
                'module_id' => $moduleId,
                'enabled'   => 1,
            ]);
        }
    }
}
