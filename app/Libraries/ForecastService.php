<?php

namespace App\Libraries;

class ForecastService
{
    public function forecast(int $tenantId, int $months = 6): array
    {
        $accounts = model(\App\Models\FinAccountModel::class)->getForTenant($tenantId);
        $balance  = array_sum(array_map(static fn ($a) => (float) $a['balance'], $accounts));

        $txnModel   = model(\App\Models\FinTransactionModel::class);
        $avgIncome  = $this->avgMonthly($tenantId, 'income', 3);
        $avgExpense = $txnModel->averageMonthlyExpense($tenantId, 3);
        $net        = $avgIncome - $avgExpense;

        $points = [];
        $cursor = $balance;

        for ($m = 1; $m <= $months; $m++) {
            $cursor += $net;
            $points[] = [
                'month'    => $m,
                'balance'  => max(0, $cursor),
                'income'   => $avgIncome,
                'expense'  => $avgExpense,
            ];
        }

        return [
            'current_balance' => $balance,
            'monthly_net'     => $net,
            'points'          => $points,
        ];
    }

    public function scenario(int $tenantId, array $params): array
    {
        $base = $this->forecast($tenantId, (int) ($params['months'] ?? 6));
        $hireCost = (float) ($params['hire_cost'] ?? 0);
        $equipment = (float) ($params['equipment_cost'] ?? 0);
        $loanIn = (float) ($params['loan_in'] ?? 0);

        $adjusted = $base['current_balance'] - $hireCost - $equipment + $loanIn;
        $net = $base['monthly_net'] - ($params['hire_monthly'] ?? 0);

        $points = [];
        $cursor = $adjusted;

        for ($m = 1; $m <= count($base['points']); $m++) {
            $cursor += $net;
            $points[] = ['month' => $m, 'balance' => max(0, $cursor)];
        }

        return ['base' => $base, 'scenario' => ['points' => $points, 'monthly_net' => $net]];
    }

    protected function avgMonthly(int $tenantId, string $type, int $months): float
    {
        $start = date('Y-m-d', strtotime('-' . $months . ' months'));
        $row   = model(\App\Models\FinTransactionModel::class)
            ->selectSum('amount', 'total')
            ->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->where('txn_date >=', $start)
            ->first();

        return $months > 0 ? (float) ($row['total'] ?? 0) / $months : 0.0;
    }
}
