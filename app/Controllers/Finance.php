<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasTenantModule;
use App\Controllers\Concerns\HasFinanceNav;
use App\Libraries\FinanceMetrics;
use App\Models\FinAccountModel;
use App\Models\FinCategoryModel;
use App\Models\FinTransactionModel;

class Finance extends BaseController
{
    use HasTenantModule;
    use HasFinanceNav;

    public function index()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        helper('date');

        $tenantId     = (int) $tenant['id'];
        $accountModel = model(FinAccountModel::class);

        if ($accountModel->where('tenant_id', $tenantId)->countAllResults() === 0) {
            $this->seedDemoFinance($tenantId);
        }

        $metrics = new FinanceMetrics(
            model(FinAccountModel::class),
            model(FinTransactionModel::class),
            model(\App\Models\FinBudgetModel::class),
            model(\App\Models\FinPaymentReminderModel::class),
            model(\App\Models\TaxPeriodModel::class),
            model(\App\Models\InsurancePolicyModel::class),
        );

        $data = $metrics->ceoDashboard($tenantId);

        return $this->render('finance/index', array_merge($data, [
            'title'          => lang('Finance.ceo_dashboard'),
            'moduleNav'      => 'overview',
            'moduleNavItems' => $this->financeNavItems(),
            'currency'       => $tenant['currency'] ?? 'IRR',
            'locale'         => session('locale') ?? 'fa',
            'breadcrumbs'    => $this->financeBreadcrumbs(),
        ]));
    }

    public function transactions()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];

        return $this->render('finance/transactions', [
            'title'          => lang('Finance.transactions'),
            'moduleNav'      => 'transactions',
            'moduleNavItems' => $this->financeNavItems(),
            'transactions'   => model(FinTransactionModel::class)->recentForTenant($tenantId, 50),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.transactions')),
        ]);
    }

    public function createTransaction()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $accounts = model(FinAccountModel::class)->getForTenant($tenantId);

        if ($accounts === []) {
            return redirect()->to('/module/finance')->with('error', lang('Finance.no_accounts'));
        }

        return $this->render('finance/create_transaction', [
            'title'          => lang('Finance.new_transaction'),
            'moduleNav'      => 'transactions',
            'moduleNavItems' => $this->financeNavItems(),
            'transaction'    => null,
            'accounts'       => $accounts,
            'categories'     => model(FinCategoryModel::class)->getForTenant($tenantId),
            'projects'       => model(\App\Models\ProjectModel::class)->getForTenant($tenantId),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.new_transaction')),
        ]);
    }

    public function storeTransaction()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        if (! $this->validate($this->transactionRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantId = (int) $tenant['id'];
        $payload  = $this->transactionPayload($tenantId);

        model(FinTransactionModel::class)->insert($payload);
        $this->applyTransactionBalances($payload, $tenantId);

        return redirect()->to('/module/finance/transactions')->with('success', lang('Finance.saved'));
    }

    public function editTransaction(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $tenantId    = (int) $tenant['id'];
        $transaction = model(FinTransactionModel::class)->findForTenant($id, $tenantId);

        if ($transaction === null) {
            return redirect()->to('/module/finance/transactions')->with('error', lang('App.not_found'));
        }

        return $this->render('finance/create_transaction', [
            'title'          => lang('Finance.edit_transaction'),
            'moduleNav'      => 'transactions',
            'moduleNavItems' => $this->financeNavItems(),
            'transaction'    => $transaction,
            'accounts'       => model(FinAccountModel::class)->getForTenant($tenantId),
            'categories'     => model(FinCategoryModel::class)->getForTenant($tenantId),
            'projects'       => model(\App\Models\ProjectModel::class)->getForTenant($tenantId),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.edit_transaction')),
        ]);
    }

    public function updateTransaction(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $tenantId    = (int) $tenant['id'];
        $txnModel    = model(FinTransactionModel::class);
        $transaction = $txnModel->findForTenant($id, $tenantId);

        if ($transaction === null) {
            return redirect()->to('/module/finance/transactions')->with('error', lang('App.not_found'));
        }

        if (! $this->validate($this->transactionRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->reverseTransactionBalances($transaction, $tenantId);

        $payload = $this->transactionPayload($tenantId);
        $txnModel->update($id, $payload);

        $this->applyTransactionBalances($payload, $tenantId);

        return redirect()->to('/module/finance/transactions')->with('success', lang('Finance.updated'));
    }

    public function deleteTransaction(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $tenantId    = (int) $tenant['id'];
        $txnModel    = model(FinTransactionModel::class);
        $transaction = $txnModel->findForTenant($id, $tenantId);

        if ($transaction === null) {
            return redirect()->to('/module/finance/transactions')->with('error', lang('App.not_found'));
        }

        $this->reverseTransactionBalances($transaction, $tenantId);

        $txnModel->delete($id);

        return redirect()->to('/module/finance/transactions')->with('success', lang('App.deleted'));
    }

    protected function transactionRules(): array
    {
        return [
            'account_id'  => 'required|integer',
            'category_id' => 'permit_empty|integer',
            'type'        => 'required|in_list[income,expense,transfer]',
            'amount'      => 'required|decimal|greater_than[0]',
            'description' => 'permit_empty|max_length[255]',
            'contact_name'=> 'permit_empty|max_length[120]',
            'txn_date'    => 'required',
            'transfer_to_account_id' => 'permit_empty|integer',
            'project_id'  => 'permit_empty|integer',
        ];
    }

    protected function transactionPayload(int $tenantId): array
    {
        $txnDate = parse_jalali_input((string) $this->request->getPost('txn_date'))
            ?? (string) $this->request->getPost('txn_date');

        return [
            'tenant_id'   => $tenantId,
            'account_id'  => (int) $this->request->getPost('account_id'),
            'transfer_to_account_id' => $this->request->getPost('transfer_to_account_id') ? (int) $this->request->getPost('transfer_to_account_id') : null,
            'category_id' => $this->request->getPost('category_id') ? (int) $this->request->getPost('category_id') : null,
            'project_id'  => $this->request->getPost('project_id') ? (int) $this->request->getPost('project_id') : null,
            'type'        => (string) $this->request->getPost('type'),
            'amount'      => (float) $this->request->getPost('amount'),
            'description' => (string) $this->request->getPost('description'),
            'contact_name'=> (string) $this->request->getPost('contact_name'),
            'txn_date'    => $txnDate,
        ];
    }

    protected function applyTransactionBalances(array $payload, int $tenantId): void
    {
        $amount = (float) $payload['amount'];
        $type   = $payload['type'];

        if ($type === 'transfer' && ! empty($payload['transfer_to_account_id'])) {
            $this->adjustAccountBalance((int) $payload['account_id'], $tenantId, 'expense', $amount);
            $this->adjustAccountBalance((int) $payload['transfer_to_account_id'], $tenantId, 'income', $amount);

            return;
        }

        $this->adjustAccountBalance((int) $payload['account_id'], $tenantId, $type, $amount);
    }

    protected function reverseTransactionBalances(array $transaction, int $tenantId): void
    {
        if ($transaction['type'] === 'transfer' && ! empty($transaction['transfer_to_account_id'])) {
            $this->adjustAccountBalance((int) $transaction['account_id'], $tenantId, 'expense', (float) $transaction['amount'], true);
            $this->adjustAccountBalance((int) $transaction['transfer_to_account_id'], $tenantId, 'income', (float) $transaction['amount'], true);

            return;
        }

        $this->adjustAccountBalance(
            (int) $transaction['account_id'],
            $tenantId,
            (string) $transaction['type'],
            (float) $transaction['amount'],
            true,
        );
    }

    protected function adjustAccountBalance(int $accountId, int $tenantId, string $type, float $amount, bool $reverse = false): void
    {
        $accountModel = model(FinAccountModel::class);
        $account      = $accountModel->find($accountId);

        if ($account === null || (int) $account['tenant_id'] !== $tenantId) {
            return;
        }

        $delta = $type === 'income' ? $amount : -$amount;

        if ($reverse) {
            $delta = -$delta;
        }

        $accountModel->update($accountId, ['balance' => (float) $account['balance'] + $delta]);
    }

    protected function seedDemoFinance(int $tenantId): void
    {
        $seeder = new \App\Database\Seeds\DemoDataSeeder(config(\Config\Database::class));
        $seeder->seedFinanceDemo($tenantId, date('Y-m-d H:i:s'));
    }
}
