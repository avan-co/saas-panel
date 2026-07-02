<?php

namespace App\Models;

use CodeIgniter\Model;

class InsurancePolicyModel extends Model
{
    protected $table         = 'insurance_policies';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'policy_number', 'provider', 'type', 'premium', 'start_date', 'end_date', 'status'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)->orderBy('end_date', 'ASC')->findAll();
    }

    public function totalPremium(int $tenantId): float
    {
        $row = $this->selectSum('premium', 'total')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        return (float) ($row['total'] ?? 0);
    }

    public function countActive(int $tenantId): int
    {
        return $this->where('tenant_id', $tenantId)->where('status', 'active')->countAllResults();
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }
}
