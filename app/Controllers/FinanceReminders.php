<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinPaymentReminderModel;

class FinanceReminders extends BaseController
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

        return $this->render('finance/reminders/index', [
            'title'          => lang('Finance.reminders'),
            'moduleNav'      => 'reminders',
            'moduleNavItems' => $this->financeNavItems(),
            'reminders'      => model(FinPaymentReminderModel::class)->getForTenant($tenantId),
            'upcoming'       => model(FinPaymentReminderModel::class)->upcoming($tenantId),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.reminders')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        helper('date');

        $rules = [
            'title'  => 'required|max_length[160]',
            'type'   => 'required|in_list[tax,insurance,rent,loan,check,contract,other]',
            'amount' => 'required|decimal|greater_than_equal_to[0]',
            'due_date' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $due = parse_jalali_input((string) $this->request->getPost('due_date')) ?? (string) $this->request->getPost('due_date');

        model(FinPaymentReminderModel::class)->insert([
            'tenant_id' => (int) $tenant['id'],
            'title'     => (string) $this->request->getPost('title'),
            'type'      => (string) $this->request->getPost('type'),
            'amount'    => (float) $this->request->getPost('amount'),
            'due_date'  => $due,
            'status'    => 'pending',
            'note'      => (string) $this->request->getPost('note'),
        ]);

        return redirect()->to('/module/finance/reminders')->with('success', lang('Finance.reminder_saved'));
    }

    public function markPaid(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null) {
            return $this->moduleDeniedRedirect();
        }

        $model    = model(FinPaymentReminderModel::class);
        $reminder = $model->findForTenant($id, (int) $tenant['id']);

        if ($reminder === null) {
            return redirect()->to('/module/finance/reminders')->with('error', lang('App.not_found'));
        }

        $model->update($id, ['status' => 'paid']);

        return redirect()->to('/module/finance/reminders')->with('success', lang('Finance.reminder_paid'));
    }
}
