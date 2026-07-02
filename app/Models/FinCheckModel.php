<?php

namespace App\Models;

use CodeIgniter\Model;

class FinCheckModel extends Model
{
    protected $table         = 'fin_checks';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'contact_id', 'direction', 'check_number', 'bank', 'amount', 'due_date', 'status', 'note'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->select('fin_checks.*, fin_contacts.name AS contact_name')
            ->join('fin_contacts', 'fin_contacts.id = fin_checks.contact_id', 'left')
            ->where('fin_checks.tenant_id', $tenantId)
            ->orderBy('due_date', 'ASC')
            ->findAll();
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
            ->like('check_number', $q)
            ->orLike('bank', $q)
            ->orLike('note', $q)
            ->groupEnd()
            ->limit($limit)
            ->findAll();
    }
}
