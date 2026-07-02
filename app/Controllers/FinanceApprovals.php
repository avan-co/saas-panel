<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\ApprovalRequestModel;
use App\Models\FinTransactionModel;

class FinanceApprovals extends BaseController
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

        $tenantId = (int) $tenant['id'];
        $pending  = model(ApprovalRequestModel::class)->pendingForTenant($tenantId);
        $txnModel = model(FinTransactionModel::class);

        foreach ($pending as &$req) {
            if ($req['entity_type'] === 'transaction') {
                $req['transaction'] = $txnModel->findForTenant((int) $req['entity_id'], $tenantId);
            }
        }

        return $this->render('finance/approvals/index', [
            'title'          => lang('Finance.approvals'),
            'moduleNav'      => 'approvals',
            'moduleNavItems' => $this->financeNavItems(),
            'requests'       => $pending,
            'canApprove'     => $this->requirePermission('finance.approve'),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.approvals')),
        ]);
    }

    public function approve(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.approve')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $req      = model(ApprovalRequestModel::class)
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->first();

        if ($req === null) {
            return redirect()->to('/module/finance/approvals')->with('error', lang('App.not_found'));
        }

        try {
            if ($req['entity_type'] === 'transaction') {
                service('financeTxn')->approve($tenantId, (int) $req['entity_id'], (int) session('user_id'));
            }
        } catch (\Throwable $e) {
            return redirect()->to('/module/finance/approvals')->with('error', $e->getMessage());
        }

        return redirect()->to('/module/finance/approvals')->with('success', lang('Finance.approval_granted'));
    }

    public function reject(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.approve')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $model    = model(ApprovalRequestModel::class);
        $req      = $model->where('id', $id)->where('tenant_id', $tenantId)->where('status', 'pending')->first();

        if ($req === null) {
            return redirect()->to('/module/finance/approvals')->with('error', lang('App.not_found'));
        }

        $model->update($id, ['status' => 'rejected', 'reviewed_by' => (int) session('user_id')]);

        if ($req['entity_type'] === 'transaction') {
            model(FinTransactionModel::class)->update((int) $req['entity_id'], ['approval_status' => 'rejected']);
        }

        service('audit')->log($tenantId, 'reject', 'approval', $id);

        return redirect()->to('/module/finance/approvals')->with('success', lang('Finance.approval_rejected'));
    }
}
