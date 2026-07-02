<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Models\FinContactModel;

class FinanceContacts extends BaseController
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

        return $this->render('finance/contacts/index', [
            'title'          => lang('Finance.contacts'),
            'moduleNav'      => 'contacts',
            'moduleNavItems' => $this->financeNavItems(),
            'contacts'       => model(FinContactModel::class)->getForTenant((int) $tenant['id']),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.contacts')),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.contacts')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        return $this->render('finance/contacts/form', [
            'title'          => lang('Finance.new_contact'),
            'moduleNav'      => 'contacts',
            'moduleNavItems' => $this->financeNavItems(),
            'contact'        => null,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.new_contact')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.contacts')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantId = (int) $tenant['id'];
        $contactId = (int) model(FinContactModel::class)->insert($this->payload($tenantId));
        $contact   = model(FinContactModel::class)->findForTenant($contactId, $tenantId);

        if ($contact !== null) {
            service('person')->syncFromContact($tenantId, $contact);
        }

        return redirect()->to('/module/finance/contacts')->with('success', lang('Finance.contact_saved'));
    }

    public function edit(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.contacts')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $contact = model(FinContactModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($contact === null) {
            return redirect()->to('/module/finance/contacts')->with('error', lang('App.not_found'));
        }

        return $this->render('finance/contacts/form', [
            'title'          => lang('Finance.edit_contact'),
            'moduleNav'      => 'contacts',
            'moduleNavItems' => $this->financeNavItems(),
            'contact'        => $contact,
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.edit_contact')),
        ]);
    }

    public function update(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.contacts')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $model   = model(FinContactModel::class);
        $contact = $model->findForTenant($id, (int) $tenant['id']);

        if ($contact === null) {
            return redirect()->to('/module/finance/contacts')->with('error', lang('App.not_found'));
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->update($id, $this->payload((int) $tenant['id']));
        $contact = $model->findForTenant($id, (int) $tenant['id']);

        if ($contact !== null) {
            service('person')->syncFromContact((int) $tenant['id'], $contact);
        }

        return redirect()->to('/module/finance/contacts')->with('success', lang('Finance.contact_updated'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.contacts')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $model   = model(FinContactModel::class);
        $contact = $model->findForTenant($id, (int) $tenant['id']);

        if ($contact === null) {
            return redirect()->to('/module/finance/contacts')->with('error', lang('App.not_found'));
        }

        $model->update($id, ['is_active' => 0]);

        return redirect()->to('/module/finance/contacts')->with('success', lang('App.deleted'));
    }

    public function show(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $contact  = model(FinContactModel::class)->findForTenant($id, $tenantId);

        if ($contact === null) {
            return redirect()->to('/module/finance/contacts')->with('error', lang('App.not_found'));
        }

        $txnModel = model(\App\Models\FinTransactionModel::class);

        return $this->render('finance/contacts/show', [
            'title'          => $contact['name'],
            'moduleNav'      => 'contacts',
            'moduleNavItems' => $this->financeNavItems(),
            'contact'        => $contact,
            'transactions'   => $txnModel->where('tenant_id', $tenantId)->where('contact_id', $id)->orderBy('txn_date', 'DESC')->limit(30)->findAll(),
            'breadcrumbs'    => $this->financeBreadcrumbs($contact['name']),
        ]);
    }

    protected function rules(): array
    {
        return [
            'name'  => 'required|max_length[160]',
            'type'  => 'required|in_list[supplier,contractor,employee,customer,both]',
            'phone' => 'permit_empty|max_length[40]',
            'email' => 'permit_empty|valid_email|max_length[120]',
        ];
    }

    protected function payload(int $tenantId): array
    {
        return [
            'tenant_id' => $tenantId,
            'name'      => (string) $this->request->getPost('name'),
            'type'      => (string) $this->request->getPost('type'),
            'phone'     => (string) $this->request->getPost('phone'),
            'email'     => (string) $this->request->getPost('email'),
            'tax_id'    => (string) $this->request->getPost('tax_id'),
            'address'   => (string) $this->request->getPost('address'),
            'balance'   => (float) ($this->request->getPost('balance') ?: 0),
            'note'      => (string) $this->request->getPost('note'),
            'is_active' => 1,
        ];
    }
}
