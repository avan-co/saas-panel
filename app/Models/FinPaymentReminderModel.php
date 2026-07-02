<?php

namespace App\Models;

use CodeIgniter\Model;

class FinPaymentReminderModel extends Model
{
    protected $table         = 'fin_payment_reminders';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'title', 'type', 'amount', 'due_date', 'status', 'note'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId, int $limit = 20): array
    {
        return $this->where('tenant_id', $tenantId)
            ->orderBy('due_date', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    public function dueTodayTotal(int $tenantId): float
    {
        $today = date('Y-m-d');
        $row   = $this->selectSum('amount', 'total')
            ->where('tenant_id', $tenantId)
            ->where('due_date', $today)
            ->where('status', 'pending')
            ->first();

        return (float) ($row['total'] ?? 0);
    }

    public function overdueCount(int $tenantId): int
    {
        return $this->where('tenant_id', $tenantId)
            ->where('due_date <', date('Y-m-d'))
            ->where('status', 'pending')
            ->countAllResults();
    }

    public function upcoming(int $tenantId, int $days = 30): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('due_date >=', date('Y-m-d'))
            ->where('due_date <=', date('Y-m-d', strtotime('+' . $days . ' days')))
            ->where('status', 'pending')
            ->orderBy('due_date', 'ASC')
            ->findAll();
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }
}
