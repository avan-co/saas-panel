<?php

namespace App\Models;

use CodeIgniter\Model;

class TaxPeriodModel extends Model
{
    protected $table         = 'tax_periods';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'period_year', 'period_quarter', 'taxable_income', 'tax_amount', 'status', 'due_date', 'finance_txn_id'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->orderBy('period_year', 'DESC')
            ->orderBy('period_quarter', 'DESC')
            ->findAll();
    }

    public function pendingAmount(int $tenantId): float
    {
        $row = $this->selectSum('tax_amount', 'total')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'filed'])
            ->first();

        return (float) ($row['total'] ?? 0);
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }
}
