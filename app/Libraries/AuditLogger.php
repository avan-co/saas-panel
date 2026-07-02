<?php

namespace App\Libraries;

class AuditLogger
{
    public function log(int $tenantId, string $action, string $entityType, ?int $entityId = null, ?string $summary = null, ?array $meta = null): void
    {
        model(\App\Models\AuditLogModel::class)->insert([
            'tenant_id'   => $tenantId,
            'user_id'     => session('user_id') ? (int) session('user_id') : null,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'summary'     => $summary,
            'meta'        => $meta !== null ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'ip_address'  => service('request')->getIPAddress(),
        ]);
    }
}
