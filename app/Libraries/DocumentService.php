<?php

namespace App\Libraries;

use App\Models\DocumentModel;

class DocumentService
{
    public function attach(int $tenantId, string $entityType, int $entityId, array $fileMeta, string $title, string $docType = 'other'): int
    {
        $docModel = model(DocumentModel::class);
        $latest   = $docModel->forEntity($tenantId, $entityType, $entityId);
        $version  = $latest !== [] ? ((int) $latest[0]['version'] + 1) : 1;

        return (int) model(DocumentModel::class)->insert([
            'tenant_id'       => $tenantId,
            'title'           => $title,
            'doc_type'        => $docType,
            'file_path'       => $fileMeta['file_path'] ?? null,
            'original_name'   => $fileMeta['original_name'] ?? null,
            'mime'            => $fileMeta['mime'] ?? null,
            'size'            => $fileMeta['size'] ?? 0,
            'entity_type'     => $entityType,
            'entity_id'       => $entityId,
            'version'         => $version,
            'approval_status' => 'draft',
            'uploaded_by'     => (int) session('user_id') ?: null,
        ]);
    }

    public function approve(int $tenantId, int $documentId, int $userId): void
    {
        $doc = model(DocumentModel::class)->findForTenant($documentId, $tenantId);

        if ($doc === null) {
            throw new \RuntimeException(lang('App.not_found'));
        }

        model(DocumentModel::class)->update($documentId, [
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s'),
            'is_locked'   => 1,
        ]);
    }
}
