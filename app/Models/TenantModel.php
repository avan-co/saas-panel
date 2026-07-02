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
        'subscription_starts_at', 'subscription_ends_at', 'deleted_at',
        'timezone', 'currency', 'fiscal_year_start',
        'economic_code', 'national_id', 'vat_registered', 'approval_threshold',
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
            ->where('t.deleted_at', null)
            ->orderBy('t.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getActiveList(): array
    {
        return $this->where('deleted_at', null)->orderBy('created_at', 'DESC')->findAll();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $builder = $this->where('slug', $slug)->where('deleted_at', null);

        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }
}
