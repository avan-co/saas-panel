<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Libraries\UploadService;
use App\Models\FinContactModel;
use App\Models\FinInvoiceFileModel;
use App\Models\FinInvoiceModel;

class FinanceInvoices extends BaseController
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

        return $this->render('finance/invoices/index', [
            'title'          => lang('Finance.invoices'),
            'moduleNav'      => 'invoices',
            'moduleNavItems' => $this->financeNavItems(),
            'invoices'       => model(FinInvoiceModel::class)->getForTenant((int) $tenant['id']),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.invoices')),
        ]);
    }

    public function create()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.invoices')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];

        return $this->render('finance/invoices/form', [
            'title'          => lang('Finance.new_invoice'),
            'moduleNav'      => 'invoices',
            'moduleNavItems' => $this->financeNavItems(),
            'invoice'        => null,
            'contacts'       => model(FinContactModel::class)->getForTenant($tenantId),
            'projects'       => model(\App\Models\ProjectModel::class)->getForTenant($tenantId),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.new_invoice')),
        ]);
    }

    public function store()
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.invoices')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantId = (int) $tenant['id'];
        $id       = model(FinInvoiceModel::class)->insert($this->payload($tenantId));
        $this->handleUpload((int) $id, $tenantId);

        return redirect()->to('/module/finance/invoices')->with('success', lang('Finance.invoice_saved'));
    }

    public function edit(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.invoices')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $invoice  = model(FinInvoiceModel::class)->findForTenant($id, $tenantId);

        if ($invoice === null) {
            return redirect()->to('/module/finance/invoices')->with('error', lang('App.not_found'));
        }

        return $this->render('finance/invoices/form', [
            'title'          => lang('Finance.edit_invoice'),
            'moduleNav'      => 'invoices',
            'moduleNavItems' => $this->financeNavItems(),
            'invoice'        => $invoice,
            'contacts'       => model(FinContactModel::class)->getForTenant($tenantId),
            'projects'       => model(\App\Models\ProjectModel::class)->getForTenant($tenantId),
            'files'          => model(FinInvoiceFileModel::class)->getForInvoice($id, $tenantId),
            'breadcrumbs'    => $this->financeBreadcrumbs(lang('Finance.edit_invoice')),
        ]);
    }

    public function update(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.invoices')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');
        $tenantId = (int) $tenant['id'];
        $model    = model(FinInvoiceModel::class);
        $invoice  = $model->findForTenant($id, $tenantId);

        if ($invoice === null) {
            return redirect()->to('/module/finance/invoices')->with('error', lang('App.not_found'));
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->update($id, $this->payload($tenantId));
        $this->handleUpload($id, $tenantId);

        return redirect()->to('/module/finance/invoices')->with('success', lang('Finance.invoice_updated'));
    }

    public function delete(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.invoices')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $model    = model(FinInvoiceModel::class);

        if ($model->findForTenant($id, $tenantId) === null) {
            return redirect()->to('/module/finance/invoices')->with('error', lang('App.not_found'));
        }

        $model->delete($id);

        return redirect()->to('/module/finance/invoices')->with('success', lang('App.deleted'));
    }

    public function download(int $fileId)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.view')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $file = model(FinInvoiceFileModel::class)
            ->where('id', $fileId)
            ->where('tenant_id', (int) $tenant['id'])
            ->first();

        if ($file === null) {
            return redirect()->back()->with('error', lang('App.not_found'));
        }

        $path = service('upload')->fullPath($file['file_path']);

        if (! is_file($path)) {
            return redirect()->back()->with('error', lang('App.not_found'));
        }

        return $this->response->download($path, null)->setFileName($file['original_name']);
    }

    protected function handleUpload(int $invoiceId, int $tenantId): void
    {
        $file = $this->request->getFile('attachment');

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return;
        }

        $stored = service('upload')->storeForTenant($tenantId, 'invoices', $file);

        if ($stored === null) {
            return;
        }

        model(FinInvoiceFileModel::class)->insert([
            'tenant_id'  => $tenantId,
            'invoice_id' => $invoiceId,
            ...$stored,
        ]);
    }

    protected function rules(): array
    {
        return [
            'number'     => 'required|max_length[60]',
            'amount'     => 'required|decimal|greater_than_equal_to[0]',
            'status'     => 'required|in_list[draft,sent,paid,overdue,cancelled]',
            'issue_date' => 'required',
            'due_date'   => 'permit_empty',
            'contact_id' => 'permit_empty|integer',
            'project_id' => 'permit_empty|integer',
        ];
    }

    protected function payload(int $tenantId): array
    {
        $issue = parse_jalali_input((string) $this->request->getPost('issue_date')) ?? (string) $this->request->getPost('issue_date');
        $due   = $this->request->getPost('due_date');
        $due   = $due ? (parse_jalali_input((string) $due) ?? (string) $due) : null;

        return [
            'tenant_id'   => $tenantId,
            'contact_id'  => $this->request->getPost('contact_id') ? (int) $this->request->getPost('contact_id') : null,
            'project_id'  => $this->request->getPost('project_id') ? (int) $this->request->getPost('project_id') : null,
            'number'      => (string) $this->request->getPost('number'),
            'amount'      => (float) $this->request->getPost('amount'),
            'status'      => (string) $this->request->getPost('status'),
            'issue_date'  => $issue,
            'due_date'    => $due,
            'description' => (string) $this->request->getPost('description'),
        ];
    }
}
