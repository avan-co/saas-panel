<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinBudgetModel;
use App\Models\FinCategoryModel;

class FinanceBudgets extends BaseController
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

        $tenantId = (int) $tenant['id'];
        $year     = (int) ($this->request->getGet('year') ?: date('Y'));
        $month    = (int) ($this->request->getGet('month') ?: date('m'));

        return $this->render('finance/budgets/index', [
            'title'          => lang('Finance.budgets'),
            'moduleNav'      => 'budgets',
            'moduleNavItems' => $this->financeNavItems(),
            'budgets'        => model(FinBudgetModel::class)->getForMonth($tenantId, $year, $month),
            'categories'     => model(FinCategoryModel::class)->getByType($tenantId, 'expense'),
            'year'           => $year,
            'month'          => $month,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.budgets')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $rules = [
            'category_id' => 'required|integer',
            'year'        => 'required|integer',
            'month'       => 'required|integer|greater_than[0]|less_than[13]',
            'amount'      => 'required|decimal|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(FinBudgetModel::class)->insert([
            'tenant_id'   => (int) $tenant['id'],
            'category_id' => (int) $this->request->getPost('category_id'),
            'year'        => (int) $this->request->getPost('year'),
            'month'       => (int) $this->request->getPost('month'),
            'amount'      => (float) $this->request->getPost('amount'),
            'note'        => (string) $this->request->getPost('note'),
        ]);

        return redirect()->to('/module/finance/budgets')->with('success', lang('Finance.budget_saved'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $model  = model(FinBudgetModel::class);
        $budget = $model->findForTenant($id, (int) $tenant['id']);

        if ($budget === null) {
            return redirect()->to('/module/finance/budgets')->with('error', lang('App.not_found'));
        }

        $model->delete($id);

        return redirect()->to('/module/finance/budgets')->with('success', lang('App.deleted'));
    }
}
