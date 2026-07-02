<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinJournalEntryModel;
use App\Models\FinJournalLineModel;
use App\Models\FinTransactionModel;

class FinanceReports extends BaseController
{
    use HasTenantModule;
    use HasFinanceNav;
    use ChecksPermission;

    public function index()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');
        $tenantId = (int) $tenant['id'];
        $txnModel = model(FinTransactionModel::class);

        return $this->render('finance/reports/index', [
            'title'          => lang('Finance.reports'),
            'moduleNav'      => 'reports',
            'moduleNavItems' => $this->financeNavItems(),
            'monthSummary'   => $txnModel->monthSummary($tenantId),
            'cashflow'       => $txnModel->dailyCashflow($tenantId, 30),
            'journal'        => model(FinJournalEntryModel::class)->getForTenant($tenantId, 30),
            'trialBalance'   => $this->trialBalance($tenantId),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.reports')),
        ]);
    }

    public function exportTransactions()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $rows     = [];
        $txns     = model(FinTransactionModel::class)->recentForTenant($tenantId, 500);

        foreach ($txns as $txn) {
            $rows[] = [
                $txn['txn_date'],
                $txn['type'],
                $txn['amount'],
                $txn['description'] ?? '',
                $txn['account_name'] ?? '',
                $txn['category_name'] ?? '',
            ];
        }

        $csv = service('export')->csv(
            [lang('Finance.date'), lang('Finance.type'), lang('Finance.amount'), lang('Finance.description'), lang('Finance.account'), lang('Finance.category')],
            $rows,
        );

        return service('export')->downloadResponse('transactions.csv', $csv);
    }

    public function exportJournal()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $entries  = model(FinJournalEntryModel::class)->getForTenant($tenantId, 200);
        $lineModel = model(FinJournalLineModel::class);
        $rows     = [];

        foreach ($entries as $entry) {
            $lines = $lineModel->where('journal_entry_id', $entry['id'])->findAll();

            foreach ($lines as $line) {
                $rows[] = [
                    $entry['entry_date'],
                    $entry['reference'] ?? '',
                    $line['account_code'],
                    $line['debit'],
                    $line['credit'],
                    $line['description'] ?? $entry['description'],
                ];
            }
        }

        $csv = service('export')->csv(
            [lang('Finance.date'), lang('Finance.reference'), lang('Finance.account_code'), lang('Finance.debit'), lang('Finance.credit'), lang('Finance.description')],
            $rows,
        );

        return service('export')->downloadResponse('journal.csv', $csv);
    }

    protected function trialBalance(int $tenantId): array
    {
        $db    = \Config\Database::connect();
        $rows  = $db->table('fin_journal_lines jl')
            ->select('jl.account_code, SUM(jl.debit) AS total_debit, SUM(jl.credit) AS total_credit')
            ->join('fin_journal_entries je', 'je.id = jl.journal_entry_id')
            ->where('je.tenant_id', $tenantId)
            ->groupBy('jl.account_code')
            ->get()
            ->getResultArray();

        $chart = model(\App\Models\FinChartAccountModel::class)->where('tenant_id', $tenantId)->findAll();
        $names = [];

        foreach ($chart as $acc) {
            $names[$acc['code']] = $acc['name'];
        }

        foreach ($rows as &$row) {
            $row['account_name'] = $names[$row['account_code']] ?? $row['account_code'];
            $row['balance']      = (float) $row['total_debit'] - (float) $row['total_credit'];
        }

        return $rows;
    }
}
