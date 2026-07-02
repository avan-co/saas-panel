<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantModel extends Model
{
    protected $table            = 'tenants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'name', 'slug', 'owner_id', 'status', 'plan',
        'timezone', 'currency', 'fiscal_year_start',
    ];
    protected $useTimestamps = true;

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    public function getForUser(int $userId): array
    {
        return $this->db->table('tenants t')
            ->select('t.*, tm.role')
            ->join('tenant_memberships tm', 'tm.tenant_id = t.id')
            ->where('tm.user_id', $userId)
            ->where('t.status !=', 'suspended')
            ->orderBy('t.name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
