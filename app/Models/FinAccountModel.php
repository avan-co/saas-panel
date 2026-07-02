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

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }

    public function balancesByType(int $tenantId): array
    {
        $rows = $this->select('type, SUM(balance) AS total')
            ->where('tenant_id', $tenantId)
            ->groupBy('type')
            ->findAll();

        $map = [];

        foreach ($rows as $row) {
            $map[$row['type']] = (float) $row['total'];
        }

        return $map;
    }
}
