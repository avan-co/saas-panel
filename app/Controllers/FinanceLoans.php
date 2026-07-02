<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinLoanModel;

class FinanceLoans extends BaseController
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

        $loanModel = model(FinLoanModel::class);
        $loans     = $loanModel->getForTenant((int) $tenant['id']);

        foreach ($loans as &$loan) {
            $loan['remaining'] = $loanModel->remainingBalance($loan);
        }

        return $this->render('finance/loans/index', [
            'title'          => lang('Finance.loans'),
            'moduleNav'      => 'loans',
            'moduleNavItems' => $this->financeNavItems(),
            'loans'          => $loans,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.loans')),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.loans')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        return $this->render('finance/loans/form', [
            'title'          => lang('Finance.new_loan'),
            'moduleNav'      => 'loans',
            'moduleNavItems' => $this->financeNavItems(),
            'loan'           => null,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.new_loan')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.loans')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(FinLoanModel::class)->insert($this->payload((int) $tenant['id']));

        return redirect()->to('/module/finance/loans')->with('success', lang('Finance.loan_saved'));
    }

    public function edit(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.loans')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $loan = model(FinLoanModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($loan === null) {
            return redirect()->to('/module/finance/loans')->with('error', lang('App.not_found'));
        }

        return $this->render('finance/loans/form', [
            'title'          => lang('Finance.edit_loan'),
            'moduleNav'      => 'loans',
            'moduleNavItems' => $this->financeNavItems(),
            'loan'           => $loan,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.edit_loan')),
        ]);
    }

    public function update(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.loans')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');
        $model = model(FinLoanModel::class);
        $loan  = $model->findForTenant($id, (int) $tenant['id']);

        if ($loan === null) {
            return redirect()->to('/module/finance/loans')->with('error', lang('App.not_found'));
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->update($id, $this->payload((int) $tenant['id']));

        return redirect()->to('/module/finance/loans')->with('success', lang('Finance.loan_updated'));
    }

    public function payInstallment(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.loans')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');
        $model = model(FinLoanModel::class);
        $loan  = $model->findForTenant($id, (int) $tenant['id']);

        if ($loan === null) {
            return redirect()->to('/module/finance/loans')->with('error', lang('App.not_found'));
        }

        $tenantId  = (int) $tenant['id'];
        $accountId = (int) ($this->request->getPost('account_id') ?: 0);
        $accounts  = model(\App\Models\FinAccountModel::class)->getForTenant($tenantId);
        $accountId = $accountId > 0 ? $accountId : (int) ($accounts[0]['id'] ?? 0);

        if ($accountId > 0) {
            try {
                service('financeTxn')->create($tenantId, [
                    'tenant_id'  => $tenantId,
                    'account_id' => $accountId,
                    'type'       => 'expense',
                    'amount'     => (float) $loan['installment_amount'],
                    'description'=> 'قسط وام ' . $loan['bank'],
                    'txn_date'   => date('Y-m-d'),
                ], $tenant);
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        $paid   = (int) $loan['paid_installments'] + 1;
        $status = $paid >= (int) $loan['total_installments'] ? 'paid' : 'active';

        $model->update($id, ['paid_installments' => $paid, 'status' => $status]);

        return redirect()->to('/module/finance/loans')->with('success', lang('Finance.installment_paid'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.loans')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $model = model(FinLoanModel::class);

        if ($model->findForTenant($id, (int) $tenant['id']) === null) {
            return redirect()->to('/module/finance/loans')->with('error', lang('App.not_found'));
        }

        $model->delete($id);

        return redirect()->to('/module/finance/loans')->with('success', lang('App.deleted'));
    }

    protected function rules(): array
    {
        return [
            'bank'               => 'required|max_length[80]',
            'principal'          => 'required|decimal|greater_than[0]',
            'interest_rate'      => 'permit_empty|decimal',
            'total_installments' => 'required|integer|greater_than[0]',
            'installment_amount' => 'required|decimal|greater_than[0]',
            'status'             => 'required|in_list[active,paid,defaulted]',
        ];
    }

    protected function payload(int $tenantId): array
    {
        $start = $this->request->getPost('start_date');
        $start = $start ? (parse_jalali_input((string) $start) ?? (string) $start) : null;

        return [
            'tenant_id'          => $tenantId,
            'bank'               => (string) $this->request->getPost('bank'),
            'principal'          => (float) $this->request->getPost('principal'),
            'interest_rate'      => (float) ($this->request->getPost('interest_rate') ?: 0),
            'total_installments' => (int) $this->request->getPost('total_installments'),
            'paid_installments'  => (int) ($this->request->getPost('paid_installments') ?: 0),
            'installment_amount' => (float) $this->request->getPost('installment_amount'),
            'start_date'         => $start,
            'status'             => (string) $this->request->getPost('status'),
            'note'               => (string) $this->request->getPost('note'),
        ];
    }
}
