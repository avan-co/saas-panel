<?php

namespace App\Models;

use CodeIgniter\Model;

class FinBudgetModel extends Model
{
    protected $table         = 'fin_budgets';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'category_id', 'year', 'month', 'amount', 'note'];
    protected $useTimestamps = true;

    public function getForMonth(int $tenantId, int $year, int $month): array
    {
        return $this->select('fin_budgets.*, fin_categories.name AS category_name, fin_categories.type AS category_type, fin_categories.color')
            ->join('fin_categories', 'fin_categories.id = fin_budgets.category_id', 'left')
            ->where('fin_budgets.tenant_id', $tenantId)
            ->where('fin_budgets.year', $year)
            ->where('fin_budgets.month', $month)
            ->orderBy('fin_categories.sort_order', 'ASC')
            ->findAll();
    }

    public function alertsForMonth(int $tenantId, int $year, int $month): array
    {
        $budgets = $this->getForMonth($tenantId, $year, $month);
        $txnModel = model(FinTransactionModel::class);
        $alerts   = [];

        foreach ($budgets as $budget) {
            if (empty($budget['category_id'])) {
                continue;
            }

            $spent = $txnModel->categorySpendInMonth($tenantId, (int) $budget['category_id'], $year, $month);
            $limit = (float) $budget['amount'];

            if ($limit <= 0) {
                continue;
            }

            $pct = round(($spent / $limit) * 100);

            if ($pct >= 70) {
                $alerts[] = [
                    'category' => $budget['category_name'] ?? '—',
                    'spent'    => $spent,
                    'budget'   => $limit,
                    'percent'  => $pct,
                ];
            }
        }

        return $alerts;
    }

    public function findForTenant(int $id, int $tenantId): ?array
    {
        $row = $this->where('id', $id)->where('tenant_id', $tenantId)->first();

        return $row ?: null;
    }
}
