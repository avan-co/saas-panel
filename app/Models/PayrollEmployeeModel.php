<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollEmployeeModel extends Model
{
    protected $table         = 'payroll_employees';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'national_id', 'job_title', 'base_salary', 'status', 'hired_at'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)->orderBy('name', 'ASC')->findAll();
    }

    public function countActive(int $tenantId): int
    {
        return $this->where('tenant_id', $tenantId)->where('status', 'active')->countAllResults();
    }

    public function totalPayroll(int $tenantId): float
    {
        $row = $this->selectSum('base_salary', 'total')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        return (float) ($row['total'] ?? 0);
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }
}
