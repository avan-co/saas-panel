<?php

namespace App\Models;

use CodeIgniter\Model;

class FinChartAccountModel extends Model
{
    protected $table         = 'fin_chart_accounts';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'code', 'name', 'type', 'is_system'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)->orderBy('code', 'ASC')->findAll();
    }
}
