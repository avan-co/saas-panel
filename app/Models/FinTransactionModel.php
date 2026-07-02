<?php

namespace App\Models;

use CodeIgniter\Model;

class FinTransactionModel extends Model
{
    protected $table         = 'fin_transactions';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'tenant_id', 'account_id', 'category_id', 'type',
        'amount', 'description', 'reference', 'txn_date',
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
}
