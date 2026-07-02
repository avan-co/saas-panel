<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinAccountModel;

class FinanceAccounts extends BaseController
{
    use HasTenantModule;
    use HasFinanceNav;

    public function index()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];

        return $this->render('finance/accounts/index', [
            'title'          => lang('Finance.accounts'),
            'moduleNav'      => 'accounts',
            'moduleNavItems' => $this->financeNavItems(),
            'accounts'       => model(FinAccountModel::class)->getForTenant($tenantId),
            'currency'       => $tenant['currency'] ?? 'IRR',
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.accounts')),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        return $this->render('finance/accounts/form', [
            'title'          => lang('Finance.new_account'),
            'moduleNav'      => 'accounts',
            'moduleNavItems' => $this->financeNavItems(),
            'account'        => null,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.new_account')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(FinAccountModel::class)->insert($this->payload((int) $tenant['id']));

        return redirect()->to('/module/finance/accounts')->with('success', lang('Finance.account_saved'));
    }

    public function edit(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $account = model(FinAccountModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($account === null) {
            return redirect()->to('/module/finance/accounts')->with('error', lang('App.not_found'));
        }

        return $this->render('finance/accounts/form', [
            'title'          => lang('Finance.edit_account'),
            'moduleNav'      => 'accounts',
            'moduleNavItems' => $this->financeNavItems(),
            'account'        => $account,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.edit_account')),
        ]);
    }

    public function update(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $model   = model(FinAccountModel::class);
        $account = $model->findForTenant($id, (int) $tenant['id']);

        if ($account === null) {
            return redirect()->to('/module/finance/accounts')->with('error', lang('App.not_found'));
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->update($id, $this->payload((int) $tenant['id']));

        return redirect()->to('/module/finance/accounts')->with('success', lang('Finance.account_updated'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $model   = model(FinAccountModel::class);
        $account = $model->findForTenant($id, (int) $tenant['id']);

        if ($account === null) {
            return redirect()->to('/module/finance/accounts')->with('error', lang('App.not_found'));
        }

        $model->delete($id);

        return redirect()->to('/module/finance/accounts')->with('success', lang('App.deleted'));
    }

    protected function rules(): array
    {
        return [
            'name'    => 'required|max_length[120]',
            'type'    => 'required|in_list[bank,cash,card,wallet,petty_cash,personal]',
            'balance' => 'permit_empty|decimal',
            'currency'=> 'required|max_length[8]',
        ];
    }

    protected function payload(int $tenantId): array
    {
        return [
            'tenant_id'  => $tenantId,
            'name'       => (string) $this->request->getPost('name'),
            'type'       => (string) $this->request->getPost('type'),
            'balance'    => (float) ($this->request->getPost('balance') ?: 0),
            'currency'   => (string) $this->request->getPost('currency'),
            'is_default' => $this->request->getPost('is_default') ? 1 : 0,
        ];
    }
}
