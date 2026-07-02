<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinAccountModel;
use App\Models\FinCategoryModel;
use App\Models\FinTransactionModel;

class Finance extends BaseController
{
    use HasTenantModule;

    public function index()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $tenantId     = (int) $tenant['id'];
        $txnModel     = model(FinTransactionModel::class);
        $accountModel = model(FinAccountModel::class);

        if ($accountModel->where('tenant_id', $tenantId)->countAllResults() === 0) {
            $this->seedDemoFinance($tenantId);
        }

        return $this->render('finance/index', [
            'title'       => lang('Finance.title'),
            'moduleNav'   => 'overview',
            'summary'     => $txnModel->monthSummary($tenantId),
            'accounts'    => $accountModel->getForTenant($tenantId),
            'recent'      => $txnModel->recentForTenant($tenantId, 8),
            'balance'     => $accountModel->totalBalance($tenantId),
            'currency'    => $tenant['currency'] ?? 'IRR',
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Finance.title')),
        ]);
    }

    public function transactions()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];

        return $this->render('finance/transactions', [
            'title'        => lang('Finance.transactions'),
            'moduleNav'    => 'transactions',
            'transactions' => model(FinTransactionModel::class)->recentForTenant($tenantId, 50),
            'breadcrumbs'  => $this->moduleBreadcrumbs(lang('Finance.title'), site_url('module/finance'), lang('Finance.transactions')),
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
            'title'       => lang('Finance.new_transaction'),
            'moduleNav'   => 'transactions',
            'transaction' => null,
            'accounts'    => $accounts,
            'categories'  => model(FinCategoryModel::class)->getForTenant($tenantId),
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Finance.title'), site_url('module/finance'), lang('Finance.new_transaction')),
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
        $this->adjustAccountBalance((int) $payload['account_id'], $tenantId, $payload['type'], (float) $payload['amount']);

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
            'title'       => lang('Finance.edit_transaction'),
            'moduleNav'   => 'transactions',
            'transaction' => $transaction,
            'accounts'    => model(FinAccountModel::class)->getForTenant($tenantId),
            'categories'  => model(FinCategoryModel::class)->getForTenant($tenantId),
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Finance.title'), site_url('module/finance'), lang('Finance.edit_transaction')),
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

        $this->adjustAccountBalance(
            (int) $transaction['account_id'],
            $tenantId,
            (string) $transaction['type'],
            (float) $transaction['amount'],
            true,
        );

        $payload = $this->transactionPayload($tenantId);
        $txnModel->update($id, $payload);

        $this->adjustAccountBalance((int) $payload['account_id'], $tenantId, $payload['type'], (float) $payload['amount']);

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

        $this->adjustAccountBalance(
            (int) $transaction['account_id'],
            $tenantId,
            (string) $transaction['type'],
            (float) $transaction['amount'],
            true,
        );

        $txnModel->delete($id);

        return redirect()->to('/module/finance/transactions')->with('success', lang('App.deleted'));
    }

    protected function transactionRules(): array
    {
        return [
            'account_id'  => 'required|integer',
            'category_id' => 'permit_empty|integer',
            'type'        => 'required|in_list[income,expense]',
            'amount'      => 'required|decimal|greater_than[0]',
            'description' => 'permit_empty|max_length[255]',
            'txn_date'    => 'required|valid_date[Y-m-d]',
        ];
    }

    protected function transactionPayload(int $tenantId): array
    {
        return [
            'tenant_id'   => $tenantId,
            'account_id'  => (int) $this->request->getPost('account_id'),
            'category_id' => $this->request->getPost('category_id') ? (int) $this->request->getPost('category_id') : null,
            'type'        => (string) $this->request->getPost('type'),
            'amount'      => (float) $this->request->getPost('amount'),
            'description' => (string) $this->request->getPost('description'),
            'txn_date'    => (string) $this->request->getPost('txn_date'),
        ];
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
