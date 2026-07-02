<?php

namespace App\Models;

use CodeIgniter\Model;

class FinTransactionModel extends Model
{
    protected $table         = 'fin_transactions';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'tenant_id', 'account_id', 'transfer_to_account_id', 'category_id', 'project_id',
        'type', 'amount', 'description', 'contact_name', 'contact_id', 'invoice_id', 'reference', 'txn_date',
    ];
    protected $useTimestamps = true;

    public function recentForTenant(int $tenantId, int $limit = 10): array
    {
        return $this->select('fin_transactions.*, fin_categories.name AS category_name, fin_categories.color AS category_color, fin_accounts.name AS account_name')
            ->join('fin_categories', 'fin_categories.id = fin_transactions.category_id', 'left')
            ->join('fin_accounts', 'fin_accounts.id = fin_transactions.account_id', 'left')
            ->where('fin_transactions.tenant_id', $tenantId)
            ->orderBy('txn_date', 'DESC')
            ->orderBy('fin_transactions.id', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function monthSummary(int $tenantId): array
    {
        $start = date('Y-m-01');
        $end   = date('Y-m-t');

        $rows = $this->select('type, SUM(amount) AS total')
            ->where('tenant_id', $tenantId)
            ->where('txn_date >=', $start)
            ->where('txn_date <=', $end)
            ->groupBy('type')
            ->findAll();

        $summary = ['income' => 0.0, 'expense' => 0.0, 'transfer' => 0.0];

        foreach ($rows as $row) {
            $summary[$row['type']] = (float) $row['total'];
        }

        return $summary;
    }

    public function averageMonthlyExpense(int $tenantId, int $months = 3): float
    {
        $start = date('Y-m-d', strtotime('-' . $months . ' months'));
        $row   = $this->selectSum('amount', 'total')
            ->where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->where('txn_date >=', $start)
            ->first();

        $total = (float) ($row['total'] ?? 0);

        return $months > 0 ? $total / $months : 0.0;
    }

    public function categorySpendInMonth(int $tenantId, int $categoryId, int $year, int $month): float
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));
        $row   = $this->selectSum('amount', 'total')
            ->where('tenant_id', $tenantId)
            ->where('category_id', $categoryId)
            ->where('type', 'expense')
            ->where('txn_date >=', $start)
            ->where('txn_date <=', $end)
            ->first();

        return (float) ($row['total'] ?? 0);
    }

    public function dailyCashflow(int $tenantId, int $days = 30): array
    {
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $rows  = $this->select('txn_date, type, SUM(amount) AS total')
            ->where('tenant_id', $tenantId)
            ->where('txn_date >=', $start)
            ->groupBy(['txn_date', 'type'])
            ->orderBy('txn_date', 'ASC')
            ->findAll();

        $map = [];

        foreach ($rows as $row) {
            $date = $row['txn_date'];
            $map[$date] ??= ['income' => 0.0, 'expense' => 0.0];
            $map[$date][$row['type']] = (float) $row['total'];
        }

        return $map;
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }

    public function forProject(int $tenantId, int $projectId, int $limit = 50): array
    {
        return $this->select('fin_transactions.*, fin_categories.name AS category_name, fin_accounts.name AS account_name')
            ->join('fin_categories', 'fin_categories.id = fin_transactions.category_id', 'left')
            ->join('fin_accounts', 'fin_accounts.id = fin_transactions.account_id', 'left')
            ->where('fin_transactions.tenant_id', $tenantId)
            ->where('fin_transactions.project_id', $projectId)
            ->orderBy('txn_date', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function projectSummary(int $tenantId, int $projectId): array
    {
        $rows = $this->select('type, SUM(amount) AS total')
            ->where('tenant_id', $tenantId)
            ->where('project_id', $projectId)
            ->groupBy('type')
            ->findAll();

        $summary = ['income' => 0.0, 'expense' => 0.0, 'transfer' => 0.0];

        foreach ($rows as $row) {
            $summary[$row['type']] = (float) $row['total'];
        }

        return $summary;
    }

    public function search(int $tenantId, string $q, int $limit = 30): array
    {
        return $this->select('fin_transactions.*, fin_categories.name AS category_name, fin_accounts.name AS account_name')
            ->join('fin_categories', 'fin_categories.id = fin_transactions.category_id', 'left')
            ->join('fin_accounts', 'fin_accounts.id = fin_transactions.account_id', 'left')
            ->where('fin_transactions.tenant_id', $tenantId)
            ->groupStart()
            ->like('fin_transactions.description', $q)
            ->orLike('fin_transactions.contact_name', $q)
            ->orLike('fin_transactions.reference', $q)
            ->orLike('fin_categories.name', $q)
            ->groupEnd()
            ->orderBy('txn_date', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
