<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinCheckModel;
use App\Models\FinContactModel;

class FinanceChecks extends BaseController
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

        return $this->render('finance/checks/index', [
            'title'          => lang('Finance.checks'),
            'moduleNav'      => 'checks',
            'moduleNavItems' => $this->financeNavItems(),
            'checks'         => model(FinCheckModel::class)->getForTenant((int) $tenant['id']),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.checks')),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.checks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        return $this->render('finance/checks/form', [
            'title'          => lang('Finance.new_check'),
            'moduleNav'      => 'checks',
            'moduleNavItems' => $this->financeNavItems(),
            'check'          => null,
            'contacts'       => model(FinContactModel::class)->getForTenant((int) $tenant['id']),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.new_check')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.checks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(FinCheckModel::class)->insert($this->payload((int) $tenant['id']));

        return redirect()->to('/module/finance/checks')->with('success', lang('Finance.check_saved'));
    }

    public function edit(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.checks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $check = model(FinCheckModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($check === null) {
            return redirect()->to('/module/finance/checks')->with('error', lang('App.not_found'));
        }

        return $this->render('finance/checks/form', [
            'title'          => lang('Finance.edit_check'),
            'moduleNav'      => 'checks',
            'moduleNavItems' => $this->financeNavItems(),
            'check'          => $check,
            'contacts'       => model(FinContactModel::class)->getForTenant((int) $tenant['id']),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.edit_check')),
        ]);
    }

    public function update(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.checks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');
        $model = model(FinCheckModel::class);
        $check = $model->findForTenant($id, (int) $tenant['id']);

        if ($check === null) {
            return redirect()->to('/module/finance/checks')->with('error', lang('App.not_found'));
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantId = (int) $tenant['id'];
        $payload  = $this->payload($tenantId);
        $prev     = $check;
        $model->update($id, $payload);

        if ($prev['status'] !== 'cleared' && $payload['status'] === 'cleared') {
            $this->recordCheckTransaction($tenantId, $payload, $tenant);
        }

        return redirect()->to('/module/finance/checks')->with('success', lang('Finance.check_updated'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.checks')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $model = model(FinCheckModel::class);

        if ($model->findForTenant($id, (int) $tenant['id']) === null) {
            return redirect()->to('/module/finance/checks')->with('error', lang('App.not_found'));
        }

        $model->delete($id);

        return redirect()->to('/module/finance/checks')->with('success', lang('App.deleted'));
    }

    protected function rules(): array
    {
        return [
            'direction'    => 'required|in_list[received,payable]',
            'check_number' => 'required|max_length[40]',
            'amount'       => 'required|decimal|greater_than[0]',
            'due_date'     => 'required',
            'status'       => 'required|in_list[pending,deposited,cleared,bounced,paid]',
        ];
    }

    protected function payload(int $tenantId): array
    {
        $due = parse_jalali_input((string) $this->request->getPost('due_date')) ?? (string) $this->request->getPost('due_date');

        return [
            'tenant_id'    => $tenantId,
            'contact_id'   => $this->request->getPost('contact_id') ? (int) $this->request->getPost('contact_id') : null,
            'direction'    => (string) $this->request->getPost('direction'),
            'check_number' => (string) $this->request->getPost('check_number'),
            'bank'         => (string) $this->request->getPost('bank'),
            'amount'       => (float) $this->request->getPost('amount'),
            'due_date'     => $due,
            'status'       => (string) $this->request->getPost('status'),
            'note'         => (string) $this->request->getPost('note'),
        ];
    }

    protected function recordCheckTransaction(int $tenantId, array $check, array $tenant): void
    {
        $accounts = model(\App\Models\FinAccountModel::class)->getForTenant($tenantId);

        if ($accounts === []) {
            return;
        }

        $type = $check['direction'] === 'received' ? 'income' : 'expense';

        try {
            service('financeTxn')->create($tenantId, [
                'tenant_id'  => $tenantId,
                'account_id' => (int) $accounts[0]['id'],
                'contact_id' => $check['contact_id'] ?? null,
                'type'       => $type,
                'amount'     => (float) $check['amount'],
                'description'=> 'چک ' . $check['check_number'],
                'txn_date'   => $check['due_date'],
            ], $tenant);
        } catch (\Throwable) {
            // period lock or approval — check status already updated
        }
    }
}
