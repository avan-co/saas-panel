<?php

namespace App\Models;

use CodeIgniter\Model;

class ApprovalRequestModel extends Model
{
    protected $table         = 'approval_requests';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'entity_type', 'entity_id', 'amount', 'status', 'requested_by', 'reviewed_by', 'note'];
    protected $useTimestamps = true;

    public function pendingForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)->where('status', 'pending')->orderBy('created_at', 'DESC')->findAll();
    }
}
