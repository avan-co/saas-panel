<?php

namespace App\Libraries;

use App\Models\FinAccountModel;
use App\Models\FinBudgetModel;
use App\Models\FinPaymentReminderModel;
use App\Models\FinTransactionModel;
use App\Models\InsurancePolicyModel;
use App\Models\TaxPeriodModel;

class FinanceMetrics
{
    public function __construct(
        protected FinAccountModel $accounts,
        protected FinTransactionModel $transactions,
        protected FinBudgetModel $budgets,
        protected FinPaymentReminderModel $reminders,
        protected TaxPeriodModel $tax,
        protected InsurancePolicyModel $insurance,
    ) {}

    public function ceoDashboard(int $tenantId): array
    {
        $accounts      = $this->accounts->getForTenant($tenantId);
        $bankBalance   = 0.0;
        $cashBalance   = 0.0;
        $totalBalance  = 0.0;

        foreach ($accounts as $account) {
            $balance = (float) $account['balance'];
            $totalBalance += $balance;

            if (in_array($account['type'], ['bank', 'card', 'personal'], true)) {
                $bankBalance += $balance;
            }

            if (in_array($account['type'], ['cash', 'petty_cash', 'wallet'], true)) {
                $cashBalance += $balance;
            }
        }

        $monthSummary = $this->transactions->monthSummary($tenantId);
        $income       = (float) ($monthSummary['income'] ?? 0);
        $expense      = (float) ($monthSummary['expense'] ?? 0);
        $net          = $income - $expense;

        $avgExpense3m = $this->transactions->averageMonthlyExpense($tenantId, 3);
        $burnRate     = $avgExpense3m > 0 ? $avgExpense3m : $expense;
        $runwayMonths = $burnRate > 0 ? round($totalBalance / $burnRate, 1) : null;

        $pendingTax       = model(TaxPeriodModel::class)->pendingAmount($tenantId);
        $upcomingInsurance = model(InsurancePolicyModel::class)->totalPremium($tenantId);
        $dueToday         = $this->reminders->dueTodayTotal($tenantId);
        $overdue          = $this->reminders->overdueCount($tenantId);
        $cashflow         = $this->transactions->dailyCashflow($tenantId, 30);
        $budgetAlerts     = $this->budgets->alertsForMonth($tenantId, (int) date('Y'), (int) date('m'));

        return [
            'accounts'           => $accounts,
            'bankBalance'        => $bankBalance,
            'cashBalance'        => $cashBalance,
            'totalBalance'       => $totalBalance,
            'monthIncome'        => $income,
            'monthExpense'       => $expense,
            'monthNet'           => $net,
            'burnRate'           => $burnRate,
            'runwayMonths'       => $runwayMonths,
            'pendingTax'         => $pendingTax,
            'upcomingInsurance'  => $upcomingInsurance,
            'dueToday'           => $dueToday,
            'overduePayments'    => $overdue,
            'cashflow'           => $cashflow,
            'budgetAlerts'       => $budgetAlerts,
            'recentTransactions' => $this->transactions->recentForTenant($tenantId, 8),
        ];
    }
}
