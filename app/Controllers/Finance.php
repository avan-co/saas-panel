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
            return redirect()->to('/dashboard');
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
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Finance.title')),
        ]);
    }

    public function transactions()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return redirect()->to('/dashboard');
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
            return redirect()->to('/dashboard');
        }

        $tenantId = (int) $tenant['id'];

        return $this->render('finance/create_transaction', [
            'title'       => lang('Finance.new_transaction'),
            'moduleNav'   => 'transactions',
            'accounts'    => model(FinAccountModel::class)->getForTenant($tenantId),
            'categories'  => model(FinCategoryModel::class)->getForTenant($tenantId),
            'breadcrumbs' => $this->moduleBreadcrumbs(lang('Finance.title'), site_url('module/finance'), lang('Finance.new_transaction')),
        ]);
    }

    public function storeTransaction()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return redirect()->to('/dashboard');
        }

        $rules = [
            'account_id'  => 'required|integer',
            'category_id' => 'permit_empty|integer',
            'type'        => 'required|in_list[income,expense]',
            'amount'      => 'required|decimal|greater_than[0]',
            'description' => 'permit_empty|max_length[255]',
            'txn_date'    => 'required|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantId = (int) $tenant['id'];
        $amount   = (float) $this->request->getPost('amount');
        $type     = (string) $this->request->getPost('type');

        model(FinTransactionModel::class)->insert([
            'tenant_id'   => $tenantId,
            'account_id'  => (int) $this->request->getPost('account_id'),
            'category_id' => $this->request->getPost('category_id') ? (int) $this->request->getPost('category_id') : null,
            'type'        => $type,
            'amount'      => $amount,
            'description' => (string) $this->request->getPost('description'),
            'txn_date'    => (string) $this->request->getPost('txn_date'),
        ]);

        $accountModel = model(FinAccountModel::class);
        $account      = $accountModel->find((int) $this->request->getPost('account_id'));

        if ($account !== null && (int) $account['tenant_id'] === $tenantId) {
            $delta   = $type === 'income' ? $amount : -$amount;
            $balance = (float) $account['balance'] + $delta;
            $accountModel->update($account['id'], ['balance' => $balance]);
        }

        return redirect()->to('/module/finance/transactions')->with('success', lang('Finance.saved'));
    }

    protected function seedDemoFinance(int $tenantId): void
    {
        $seeder = new \App\Database\Seeds\DemoDataSeeder(config(\Config\Database::class));
        $seeder->seedFinanceDemo($tenantId, date('Y-m-d H:i:s'));
    }
}
