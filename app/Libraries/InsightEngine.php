<?php

namespace App\Libraries;

class InsightEngine
{
    public function analyze(int $tenantId): array
    {
        $insights = [];
        $txnModel = model(\App\Models\FinTransactionModel::class);

        $avg3m = $txnModel->averageMonthlyExpense($tenantId, 3);
        $month = $txnModel->monthSummary($tenantId);
        $currentExpense = (float) ($month['expense'] ?? 0);

        if ($avg3m > 0 && $currentExpense > $avg3m * 1.3) {
            $insights[] = [
                'type'    => 'warning',
                'title'   => lang('Insights.expense_spike'),
                'body'    => lang('Insights.expense_spike_body', ['percent' => round(($currentExpense / $avg3m - 1) * 100)]),
            ];
        }

        $metrics = new FinanceMetrics(
            model(\App\Models\FinAccountModel::class),
            $txnModel,
            model(\App\Models\FinBudgetModel::class),
            model(\App\Models\FinPaymentReminderModel::class),
            model(\App\Models\TaxPeriodModel::class),
            model(\App\Models\InsurancePolicyModel::class),
        );
        $dash = $metrics->ceoDashboard($tenantId);

        if ($dash['runwayMonths'] !== null && $dash['runwayMonths'] < 3) {
            $insights[] = [
                'type'  => 'critical',
                'title' => lang('Insights.low_runway'),
                'body'  => lang('Insights.low_runway_body', ['months' => $dash['runwayMonths']]),
            ];
        }

        foreach ($dash['budgetAlerts'] as $alert) {
            if ($alert['percent'] >= 100) {
                $insights[] = [
                    'type'  => 'warning',
                    'title' => lang('Insights.budget_exceeded'),
                    'body'  => $alert['category'] . ' — ' . $alert['percent'] . '%',
                ];
            }
        }

        return $insights;
    }
}
