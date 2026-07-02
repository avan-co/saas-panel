<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table         = 'audit_logs';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'user_id', 'action', 'entity_type', 'entity_id', 'summary', 'meta', 'ip_address'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getForTenant(int $tenantId, int $limit = 100): array
    {
        return $this->where('tenant_id', $tenantId)->orderBy('created_at', 'DESC')->limit($limit)->findAll();
    }
}
