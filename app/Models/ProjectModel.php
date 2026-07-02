<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table         = 'projects';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'code', 'client_name', 'status', 'budget', 'progress', 'start_date', 'end_date'];
    protected $useTimestamps = true;

    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)->orderBy('status', 'ASC')->orderBy('name', 'ASC')->findAll();
    }

    public function countActive(int $tenantId): int
    {
        return $this->where('tenant_id', $tenantId)->whereIn('status', ['planning', 'active'])->countAllResults();
    }

    public function totalBudget(int $tenantId): float
    {
        $row = $this->selectSum('budget', 'total')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['planning', 'active', 'on_hold'])
            ->first();

        return (float) ($row['total'] ?? 0);
    }

    public function averageProgress(int $tenantId): int
    {
        $row = $this->selectAvg('progress', 'avg')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'on_hold'])
            ->first();

        return (int) round((float) ($row['avg'] ?? 0));
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }

    public function financialSummary(int $tenantId, int $projectId): array
    {
        $txn = model(FinTransactionModel::class)->projectSummary($tenantId, $projectId);
        $income  = (float) ($txn['income'] ?? 0);
        $expense = (float) ($txn['expense'] ?? 0);

        return [
            'income'  => $income,
            'expense' => $expense,
            'profit'  => $income - $expense,
        ];
    }

    public function search(int $tenantId, string $q, int $limit = 20): array
    {
        return $this->where('tenant_id', $tenantId)
            ->groupStart()
            ->like('name', $q)
            ->orLike('code', $q)
            ->orLike('client_name', $q)
            ->groupEnd()
            ->limit($limit)
            ->findAll();
    }
}
