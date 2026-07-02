<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonModel extends Model
{
    protected $table         = 'persons';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'national_id', 'phone', 'email', 'address', 'note'];
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

    public function findByNationalId(int $tenantId, string $nationalId): ?array
    {
        if ($nationalId === '') {
            return null;
        }

        $row = $this->where('tenant_id', $tenantId)->where('national_id', $nationalId)->first();

        return $row ?: null;
    }

    public function countForTenant(int $tenantId): int
    {
        return $this->where('tenant_id', $tenantId)->countAllResults();
    }
}
