<?php

namespace App\Libraries;

use App\Models\FinAccountModel;
use App\Models\FinCategoryModel;
use App\Models\FinInvoiceModel;
use App\Models\FinTransactionModel;
use App\Models\InsurancePolicyModel;
use App\Models\PayrollEmployeeModel;
use App\Models\PayrollRunItemModel;
use App\Models\PayrollRunModel;
use App\Models\ProjectModel;
use App\Models\TaxPeriodModel;
use App\Models\TimesheetModel;

class ErpIntegrationService
{
    public function onTimesheetRecorded(int $tenantId, int $timesheetId, array $tenant): void
    {
        if (! service('tenantContext')->hasModule('finance')) {
            $this->updateProjectLaborCost($tenantId, $timesheetId);

            return;
        }

        $sheet = model(TimesheetModel::class)->findForTenant($timesheetId, $tenantId);

        if ($sheet === null) {
            return;
        }

        $existing = $this->findSourceTransaction($tenantId, 'timesheet', $timesheetId);

        $payload = [
            'tenant_id'   => $tenantId,
            'account_id'  => $this->defaultAccountId($tenantId),
            'category_id' => $this->laborCategoryId($tenantId),
            'project_id'  => (int) $sheet['project_id'],
            'employee_id' => (int) $sheet['employee_id'],
            'type'        => 'expense',
            'amount'      => (float) $sheet['labor_cost'],
            'description' => sprintf('هزینه نیروی انسانی — %s ساعت', $sheet['hours']),
            'txn_date'    => (string) $sheet['work_date'],
            'source_type' => 'timesheet',
            'source_id'   => $timesheetId,
        ];

        if ($existing !== null) {
            service('financeTxn')->update($tenantId, (int) $existing['id'], $payload, $tenant);

            return;
        }

        service('financeTxn')->create($tenantId, $payload, $tenant);
        $this->updateProjectLaborCost($tenantId, $timesheetId);
    }

    public function onPayrollApproved(int $tenantId, int $runId, array $tenant): void
    {
        $run = model(PayrollRunModel::class)->findForTenant($runId, $tenantId);

        if ($run === null || ! empty($run['finance_txn_id'])) {
            return;
        }

        if (! service('tenantContext')->hasModule('finance')) {
            return;
        }

        $items           = model(PayrollRunItemModel::class)->getForRun($runId);
        $totalNet        = 0.0;
        $totalInsurance  = 0.0;
        $totalTax        = 0.0;
        $totalGross      = 0.0;

        foreach ($items as $item) {
            $totalNet       += (float) $item['net_pay'];
            $totalInsurance += (float) $item['insurance_employer'];
            $totalTax       += (float) $item['tax_amount'];
            $totalGross     += (float) $item['base_salary'];
        }

        $txnDate = date('Y-m-d');
        $account = $this->defaultAccountId($tenantId);

        $payrollTxnId = service('financeTxn')->create($tenantId, [
            'tenant_id'   => $tenantId,
            'account_id'  => $account,
            'category_id' => $this->payrollCategoryId($tenantId),
            'type'        => 'expense',
            'amount'      => $totalNet,
            'description' => sprintf('واریز حقوق %d/%02d', $run['period_year'], $run['period_month']),
            'txn_date'    => $txnDate,
            'source_type' => 'payroll_run',
            'source_id'   => $runId,
        ], $tenant);

        $insuranceTxnId = null;
        $taxTxnId       = null;

        if ($totalInsurance > 0) {
            $insuranceTxnId = service('financeTxn')->create($tenantId, [
                'tenant_id'   => $tenantId,
                'account_id'  => $account,
                'category_id' => $this->insuranceCategoryId($tenantId),
                'type'        => 'expense',
                'amount'      => $totalInsurance,
                'description' => sprintf('بدهی بیمه حقوق %d/%02d', $run['period_year'], $run['period_month']),
                'txn_date'    => $txnDate,
                'source_type' => 'payroll_insurance',
                'source_id'   => $runId,
            ], $tenant);
        }

        if ($totalTax > 0) {
            $taxTxnId = service('financeTxn')->create($tenantId, [
                'tenant_id'   => $tenantId,
                'account_id'  => $account,
                'category_id' => $this->taxCategoryId($tenantId),
                'type'        => 'expense',
                'amount'      => $totalTax,
                'description' => sprintf('مالیات حقوق %d/%02d', $run['period_year'], $run['period_month']),
                'txn_date'    => $txnDate,
                'source_type' => 'payroll_tax',
                'source_id'   => $runId,
            ], $tenant);
        }

        model(PayrollRunModel::class)->update($runId, [
            'finance_txn_id'   => $payrollTxnId,
            'insurance_txn_id' => $insuranceTxnId,
            'tax_txn_id'       => $taxTxnId,
        ]);

        $this->allocatePayrollToProjects($tenantId, $run);
        $this->createTaxPeriodFromPayroll($tenantId, $run, $totalTax);
    }

    public function onTaxPaid(int $tenantId, int $periodId, int $accountId, array $tenant): void
    {
        $period = model(TaxPeriodModel::class)->findForTenant($periodId, $tenantId);

        if ($period === null || $period['status'] === 'paid' || ! empty($period['finance_txn_id'])) {
            return;
        }

        if (! service('tenantContext')->hasModule('finance')) {
            model(TaxPeriodModel::class)->update($periodId, ['status' => 'paid']);

            return;
        }

        $txnId = service('financeTxn')->create($tenantId, [
            'tenant_id'   => $tenantId,
            'account_id'  => $accountId,
            'category_id' => $this->taxCategoryId($tenantId),
            'type'        => 'expense',
            'amount'      => (float) $period['tax_amount'],
            'description' => sprintf('پرداخت مالیات فصل %d سال %d', $period['period_quarter'], $period['period_year']),
            'txn_date'    => date('Y-m-d'),
            'source_type' => 'tax_period',
            'source_id'   => $periodId,
        ], $tenant);

        model(TaxPeriodModel::class)->update($periodId, [
            'status'         => 'paid',
            'finance_txn_id' => $txnId,
        ]);
    }

    public function onInsurancePremiumPaid(int $tenantId, int $policyId, int $accountId, array $tenant): void
    {
        $policy = model(InsurancePolicyModel::class)->findForTenant($policyId, $tenantId);

        if ($policy === null || ! empty($policy['finance_txn_id'])) {
            return;
        }

        if (! service('tenantContext')->hasModule('finance')) {
            return;
        }

        $txnId = service('financeTxn')->create($tenantId, [
            'tenant_id'   => $tenantId,
            'account_id'  => $accountId,
            'category_id' => $this->insuranceCategoryId($tenantId),
            'type'        => 'expense',
            'amount'      => (float) $policy['premium'],
            'description' => 'پرداخت بیمه ' . $policy['policy_number'],
            'txn_date'    => date('Y-m-d'),
            'source_type' => 'insurance_policy',
            'source_id'   => $policyId,
        ], $tenant);

        model(InsurancePolicyModel::class)->update($policyId, ['finance_txn_id' => $txnId]);
    }

    public function onInvoiceRecorded(int $tenantId, int $invoiceId, array $tenant): void
    {
        if (! service('tenantContext')->hasModule('finance')) {
            return;
        }

        $invoice = model(FinInvoiceModel::class)->findForTenant($invoiceId, $tenantId);

        if ($invoice === null || in_array($invoice['status'], ['draft', 'cancelled'], true)) {
            return;
        }

        if ($this->findSourceTransaction($tenantId, 'invoice', $invoiceId) !== null) {
            return;
        }

        $direction = $invoice['direction'] ?? 'sale';
        $type      = $direction === 'purchase' ? 'expense' : 'income';

        service('financeTxn')->create($tenantId, [
            'tenant_id'   => $tenantId,
            'account_id'  => $this->defaultAccountId($tenantId),
            'project_id'  => $invoice['project_id'] ? (int) $invoice['project_id'] : null,
            'contact_id'  => $invoice['contact_id'] ? (int) $invoice['contact_id'] : null,
            'invoice_id'  => $invoiceId,
            'type'        => $type,
            'amount'      => (float) $invoice['amount'],
            'description' => ($direction === 'purchase' ? 'فاکتور خرید ' : 'فاکتور فروش ') . $invoice['number'],
            'txn_date'    => (string) $invoice['issue_date'],
            'source_type' => 'invoice',
            'source_id'   => $invoiceId,
        ], $tenant);

        if (! empty($invoice['project_id'])) {
            $this->refreshProjectCosts($tenantId, (int) $invoice['project_id']);
        }

        if ($direction === 'sale' && (float) ($invoice['vat_amount'] ?? 0) > 0) {
            $this->syncVatTaxPeriod($tenantId, $invoice);
        }
    }

    public function integrationStats(int $tenantId): array
    {
        $txnModel = model(FinTransactionModel::class);

        return [
            'persons'           => model(\App\Models\PersonModel::class)->countForTenant($tenantId),
            'documents'         => model(\App\Models\DocumentModel::class)->countForTenant($tenantId),
            'timesheets'        => model(TimesheetModel::class)->countForTenant($tenantId),
            'linked_txns'       => $txnModel->where('tenant_id', $tenantId)->where('source_type IS NOT NULL', null, false)->countAllResults(),
            'payroll_linked'    => model(PayrollRunModel::class)->where('tenant_id', $tenantId)->where('finance_txn_id IS NOT NULL', null, false)->countAllResults(),
            'tax_linked'        => model(TaxPeriodModel::class)->where('tenant_id', $tenantId)->where('finance_txn_id IS NOT NULL', null, false)->countAllResults(),
            'insurance_linked'  => model(InsurancePolicyModel::class)->where('tenant_id', $tenantId)->where('finance_txn_id IS NOT NULL', null, false)->countAllResults(),
        ];
    }

    protected function allocatePayrollToProjects(int $tenantId, array $run): void
    {
        if (! service('tenantContext')->hasModule('projects')) {
            return;
        }

        $hoursMap = model(TimesheetModel::class)->hoursByProjectForMonth(
            $tenantId,
            (int) $run['period_year'],
            (int) $run['period_month'],
        );

        foreach (array_keys($hoursMap) as $projectId) {
            $this->refreshProjectCosts($tenantId, $projectId);
        }
    }

    protected function updateProjectLaborCost(int $tenantId, int $timesheetId): void
    {
        $sheet = model(TimesheetModel::class)->findForTenant($timesheetId, $tenantId);

        if ($sheet === null) {
            return;
        }

        $this->refreshProjectCosts($tenantId, (int) $sheet['project_id']);
    }

    protected function refreshProjectCosts(int $tenantId, int $projectId): void
    {
        $summary = model(FinTransactionModel::class)->projectSummary($tenantId, $projectId);
        $labor   = model(TimesheetModel::class)
            ->selectSum('labor_cost', 'total')
            ->where('tenant_id', $tenantId)
            ->where('project_id', $projectId)
            ->first();

        model(ProjectModel::class)->update($projectId, [
            'actual_cost' => (float) ($summary['expense'] ?? 0),
            'labor_cost'  => (float) ($labor['total'] ?? 0),
        ]);
    }

    protected function createTaxPeriodFromPayroll(int $tenantId, array $run, float $taxAmount): void
    {
        if ($taxAmount <= 0 || ! service('tenantContext')->hasModule('tax')) {
            return;
        }

        $quarter = (int) ceil((int) $run['period_month'] / 3);
        $exists  = model(TaxPeriodModel::class)
            ->where('tenant_id', $tenantId)
            ->where('period_year', $run['period_year'])
            ->where('period_quarter', $quarter)
            ->where('status !=', 'paid')
            ->first();

        if ($exists !== null) {
            model(TaxPeriodModel::class)->update($exists['id'], [
                'tax_amount' => (float) $exists['tax_amount'] + $taxAmount,
            ]);

            return;
        }

        model(TaxPeriodModel::class)->insert([
            'tenant_id'      => $tenantId,
            'period_year'    => $run['period_year'],
            'period_quarter' => $quarter,
            'taxable_income' => 0,
            'tax_amount'     => $taxAmount,
            'status'         => 'pending',
        ]);
    }

    protected function syncVatTaxPeriod(int $tenantId, array $invoice): void
    {
        if (! service('tenantContext')->hasModule('tax')) {
            return;
        }

        $issue    = strtotime((string) $invoice['issue_date']);
        $year     = (int) date('Y', $issue);
        $quarter  = (int) ceil((int) date('n', $issue) / 3);
        $vat      = (float) $invoice['vat_amount'];
        $existing = model(TaxPeriodModel::class)
            ->where('tenant_id', $tenantId)
            ->where('period_year', $year)
            ->where('period_quarter', $quarter)
            ->first();

        if ($existing !== null) {
            model(TaxPeriodModel::class)->update($existing['id'], [
                'tax_amount'     => (float) $existing['tax_amount'] + $vat,
                'taxable_income' => (float) $existing['taxable_income'] + (float) ($invoice['subtotal'] ?? 0),
            ]);

            return;
        }

        model(TaxPeriodModel::class)->insert([
            'tenant_id'      => $tenantId,
            'period_year'    => $year,
            'period_quarter' => $quarter,
            'taxable_income' => (float) ($invoice['subtotal'] ?? 0),
            'tax_amount'     => $vat,
            'status'         => 'pending',
        ]);
    }

    protected function findSourceTransaction(int $tenantId, string $sourceType, int $sourceId): ?array
    {
        $row = model(FinTransactionModel::class)
            ->where('tenant_id', $tenantId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->first();

        return $row ?: null;
    }

    protected function defaultAccountId(int $tenantId): int
    {
        $accounts = model(FinAccountModel::class)->getForTenant($tenantId);

        if ($accounts === []) {
            throw new \RuntimeException(lang('Finance.no_accounts'));
        }

        foreach ($accounts as $account) {
            if ((int) ($account['is_default'] ?? 0) === 1) {
                return (int) $account['id'];
            }
        }

        return (int) $accounts[0]['id'];
    }

    protected function laborCategoryId(int $tenantId): ?int
    {
        return $this->ensureCategory($tenantId, 'نیروی انسانی', '#6366f1');
    }

    protected function payrollCategoryId(int $tenantId): ?int
    {
        return $this->ensureCategory($tenantId, 'حقوق و دستمزد', '#8b5cf6');
    }

    protected function insuranceCategoryId(int $tenantId): ?int
    {
        return $this->ensureCategory($tenantId, 'بیمه', '#0ea5e9');
    }

    protected function taxCategoryId(int $tenantId): ?int
    {
        return $this->ensureCategory($tenantId, 'مالیات', '#f59e0b');
    }

    protected function ensureCategory(int $tenantId, string $name, string $color): ?int
    {
        $model = model(FinCategoryModel::class);
        $row   = $model->where('tenant_id', $tenantId)->where('name', $name)->first();

        if ($row !== null) {
            return (int) $row['id'];
        }

        return (int) $model->insert([
            'tenant_id' => $tenantId,
            'name'      => $name,
            'type'      => 'expense',
            'color'     => $color,
        ]);
    }
}
