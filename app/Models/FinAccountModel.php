<?php

namespace App\Models;

use CodeIgniter\Model;

class FinAccountModel extends Model
{
    protected $table         = 'fin_accounts';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'type', 'balance', 'currency', 'is_default'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->orderBy('is_default', 'DESC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function totalBalance(int $tenantId): float
    {
        $row = $this->selectSum('balance', 'total')
            ->where('tenant_id', $tenantId)
            ->first();

        return (float) ($row['total'] ?? 0);
    }
}
