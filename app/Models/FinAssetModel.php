<?php

namespace App\Models;

use CodeIgniter\Model;

class FinAssetModel extends Model
{
    protected $table         = 'fin_assets';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'tenant_id', 'name', 'category', 'purchase_price', 'purchase_date',
        'custodian', 'location', 'serial_number', 'status', 'note',
    ];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)->orderBy('name', 'ASC')->findAll();
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }

    public function search(int $tenantId, string $q, int $limit = 20): array
    {
        return $this->where('tenant_id', $tenantId)
            ->groupStart()
            ->like('name', $q)
            ->orLike('category', $q)
            ->orLike('serial_number', $q)
            ->orLike('custodian', $q)
            ->orLike('location', $q)
            ->groupEnd()
            ->limit($limit)
            ->findAll();
    }
}
