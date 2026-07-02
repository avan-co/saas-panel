<?php

namespace App\Controllers;

use App\Models\FinAccountModel;
use App\Models\FinCategoryModel;
use App\Models\FinTransactionModel;

class Finance extends BaseController
{
    public function index()
    {
        $tenant = $this->requireFinanceModule();

        if ($tenant === null) {
            return redirect()->to('/dashboard');
        }

        $tenantId = (int) $tenant['id'];

        $txnModel     = model(FinTransactionModel::class);
        $accountModel = model(FinAccountModel::class);

        if ($accountModel->where('tenant_id', $tenantId)->countAllResults() === 0) {
            $this->seedDemoFinance($tenantId);
        }

        $summary   = $txnModel->monthSummary($tenantId);
        $accounts  = $accountModel->getForTenant($tenantId);
        $recent    = $txnModel->recentForTenant($tenantId, 8);
        $balance   = $accountModel->totalBalance($tenantId);

        return $this->render('finance/index', [
            'title'       => lang('Finance.title'),
            'moduleNav'   => 'overview',
            'summary'     => $summary,
            'accounts'    => $accounts,
            'recent'      => $recent,
            'balance'     => $balance,
            'breadcrumbs' => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Finance.title')],
            ],
        ]);
    }

    public function transactions()
    {
        $tenant = $this->requireFinanceModule();

        if ($tenant === null) {
            return redirect()->to('/dashboard');
        }

        $tenantId = (int) $tenant['id'];
        $txnModel = model(FinTransactionModel::class);

        return $this->render('finance/transactions', [
            'title'        => lang('Finance.transactions'),
            'moduleNav'    => 'transactions',
            'transactions' => $txnModel->recentForTenant($tenantId, 50),
            'breadcrumbs'  => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Finance.title'), 'url' => site_url('module/finance')],
                ['label' => lang('Finance.transactions')],
            ],
        ]);
    }

    protected function requireFinanceModule(): ?array
    {
        $tenantContext = service('tenantContext');

        if (! $tenantContext->hasModule('finance')) {
            return null;
        }

        $db = \Config\Database::connect();

        if (! $db->tableExists('fin_accounts')) {
            try {
                \Config\Services::migrations()->latest();
            } catch (\Throwable) {
                return null;
            }
        }

        return $tenantContext->getTenant();
    }

    protected function seedDemoFinance(int $tenantId): void
    {
        $seeder = new \App\Database\Seeds\DemoDataSeeder(config(\Config\Database::class));
        $seeder->seedFinanceDemo($tenantId, date('Y-m-d H:i:s'));
    }
}
