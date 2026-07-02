<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollRunModel extends Model
{
    protected $table         = 'payroll_runs';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'period_year', 'period_month', 'total_amount', 'employee_count', 'status', 'paid_at'];
    protected $useTimestamps = true;

    public function recentForTenant(int $tenantId, int $limit = 6): array
    {
        return $this->where('tenant_id', $tenantId)
            ->orderBy('period_year', 'DESC')
            ->orderBy('period_month', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }
}
