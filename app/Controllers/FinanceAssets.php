<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinAssetModel;

class FinanceAssets extends BaseController
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

        return $this->render('finance/assets/index', [
            'title'          => lang('Finance.assets'),
            'moduleNav'      => 'assets',
            'moduleNavItems' => $this->financeNavItems(),
            'assets'         => model(FinAssetModel::class)->getForTenant((int) $tenant['id']),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.assets')),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.assets')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        return $this->render('finance/assets/form', [
            'title'          => lang('Finance.new_asset'),
            'moduleNav'      => 'assets',
            'moduleNavItems' => $this->financeNavItems(),
            'asset'          => null,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.new_asset')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.assets')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(FinAssetModel::class)->insert($this->payload((int) $tenant['id']));

        return redirect()->to('/module/finance/assets')->with('success', lang('Finance.asset_saved'));
    }

    public function edit(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.assets')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $asset = model(FinAssetModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($asset === null) {
            return redirect()->to('/module/finance/assets')->with('error', lang('App.not_found'));
        }

        return $this->render('finance/assets/form', [
            'title'          => lang('Finance.edit_asset'),
            'moduleNav'      => 'assets',
            'moduleNavItems' => $this->financeNavItems(),
            'asset'          => $asset,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.edit_asset')),
        ]);
    }

    public function update(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.assets')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');
        $model = model(FinAssetModel::class);
        $asset = $model->findForTenant($id, (int) $tenant['id']);

        if ($asset === null) {
            return redirect()->to('/module/finance/assets')->with('error', lang('App.not_found'));
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->update($id, $this->payload((int) $tenant['id']));

        return redirect()->to('/module/finance/assets')->with('success', lang('Finance.asset_updated'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.assets')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $model = model(FinAssetModel::class);

        if ($model->findForTenant($id, (int) $tenant['id']) === null) {
            return redirect()->to('/module/finance/assets')->with('error', lang('App.not_found'));
        }

        $model->delete($id);

        return redirect()->to('/module/finance/assets')->with('success', lang('App.deleted'));
    }

    protected function rules(): array
    {
        return [
            'name'           => 'required|max_length[160]',
            'purchase_price' => 'required|decimal|greater_than_equal_to[0]',
            'status'         => 'required|in_list[active,disposed]',
        ];
    }

    protected function payload(int $tenantId): array
    {
        $purchaseDate = $this->request->getPost('purchase_date');
        $purchaseDate = $purchaseDate ? (parse_jalali_input((string) $purchaseDate) ?? (string) $purchaseDate) : null;

        return [
            'tenant_id'      => $tenantId,
            'name'           => (string) $this->request->getPost('name'),
            'category'       => (string) $this->request->getPost('category'),
            'purchase_price' => (float) $this->request->getPost('purchase_price'),
            'purchase_date'  => $purchaseDate,
            'custodian'      => (string) $this->request->getPost('custodian'),
            'location'       => (string) $this->request->getPost('location'),
            'serial_number'  => (string) $this->request->getPost('serial_number'),
            'status'         => (string) $this->request->getPost('status'),
            'note'           => (string) $this->request->getPost('note'),
        ];
    }
}
