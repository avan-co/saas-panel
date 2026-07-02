<?php

namespace App\Models;

use CodeIgniter\Model;

class PeriodLockModel extends Model
{
    protected $table         = 'period_locks';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'year', 'month', 'locked_by', 'locked_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)->orderBy('year', 'DESC')->orderBy('month', 'DESC')->findAll();
    }
}
