<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table         = 'documents';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'tenant_id', 'title', 'doc_type', 'file_path', 'original_name', 'mime', 'size',
        'entity_type', 'entity_id', 'version', 'is_locked', 'approval_status', 'approved_by', 'approved_at', 'uploaded_by',
    ];
    protected $useTimestamps = true;

    public function forEntity(int $tenantId, string $entityType, int $entityId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('version', 'DESC')
            ->findAll();
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }

    public function countForTenant(int $tenantId): int
    {
        return $this->where('tenant_id', $tenantId)->countAllResults();
    }
}
