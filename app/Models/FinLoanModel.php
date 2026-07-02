<?php

namespace App\Models;

use CodeIgniter\Model;

class FinLoanModel extends Model
{
    protected $table         = 'fin_loans';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'tenant_id', 'bank', 'principal', 'interest_rate', 'total_installments',
        'paid_installments', 'installment_amount', 'start_date', 'status', 'note',
    ];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)->orderBy('status', 'ASC')->orderBy('start_date', 'DESC')->findAll();
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }

    public function remainingBalance(array $loan): float
    {
        $remaining = (int) $loan['total_installments'] - (int) $loan['paid_installments'];

        return max(0, $remaining * (float) $loan['installment_amount']);
    }

    public function search(int $tenantId, string $q, int $limit = 20): array
    {
        return $this->where('tenant_id', $tenantId)
            ->groupStart()
            ->like('bank', $q)
            ->orLike('note', $q)
            ->groupEnd()
            ->limit($limit)
            ->findAll();
    }
}
