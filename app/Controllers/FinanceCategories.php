<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinCategoryModel;

class FinanceCategories extends BaseController
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
        $model    = model(FinCategoryModel::class);

        return $this->render('finance/categories/index', [
            'title'          => lang('Finance.categories'),
            'moduleNav'      => 'categories',
            'moduleNavItems' => $this->financeNavItems(),
            'income'         => $model->getByType($tenantId, 'income'),
            'expense'        => $model->getByType($tenantId, 'expense'),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.categories')),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        return $this->render('finance/categories/form', [
            'title'          => lang('Finance.new_category'),
            'moduleNav'      => 'categories',
            'moduleNavItems' => $this->financeNavItems(),
            'category'       => null,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.new_category')),
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

        model(FinCategoryModel::class)->insert($this->payload((int) $tenant['id']));

        return redirect()->to('/module/finance/categories')->with('success', lang('Finance.category_saved'));
    }

    public function edit(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $category = model(FinCategoryModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($category === null) {
            return redirect()->to('/module/finance/categories')->with('error', lang('App.not_found'));
        }

        return $this->render('finance/categories/form', [
            'title'          => lang('Finance.edit_category'),
            'moduleNav'      => 'categories',
            'moduleNavItems' => $this->financeNavItems(),
            'category'       => $category,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.edit_category')),
        ]);
    }

    public function update(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $model    = model(FinCategoryModel::class);
        $category = $model->findForTenant($id, (int) $tenant['id']);

        if ($category === null) {
            return redirect()->to('/module/finance/categories')->with('error', lang('App.not_found'));
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->update($id, $this->payload((int) $tenant['id']));

        return redirect()->to('/module/finance/categories')->with('success', lang('Finance.category_updated'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $model    = model(FinCategoryModel::class);
        $category = $model->findForTenant($id, (int) $tenant['id']);

        if ($category === null) {
            return redirect()->to('/module/finance/categories')->with('error', lang('App.not_found'));
        }

        $model->update($id, ['is_active' => 0]);

        return redirect()->to('/module/finance/categories')->with('success', lang('App.deleted'));
    }

    protected function rules(): array
    {
        return [
            'name'  => 'required|max_length[120]',
            'type'  => 'required|in_list[income,expense]',
            'color' => 'permit_empty|max_length[20]',
        ];
    }

    protected function payload(int $tenantId): array
    {
        return [
            'tenant_id'  => $tenantId,
            'name'       => (string) $this->request->getPost('name'),
            'type'       => (string) $this->request->getPost('type'),
            'color'      => (string) ($this->request->getPost('color') ?: '#64748b'),
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 0),
            'is_active'  => 1,
        ];
    }
}
