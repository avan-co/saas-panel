<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasFinanceNav;
use App\Controllers\Concerns\HasTenantModule;
use App\Libraries\UploadService;
use App\Models\FinContactModel;
use App\Models\FinInvoiceFileModel;
use App\Models\FinInvoiceLineModel;
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
            'lines'          => [],
            'contacts'       => model(FinContactModel::class)->getForTenant($tenantId),
            'projects'       => model(\App\Models\ProjectModel::class)->getForTenant($tenantId),
            'vatRate'        => 10,
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
        $payload  = $this->payload($tenantId);
        $id       = model(FinInvoiceModel::class)->insert($payload);
        $this->saveLines((int) $id, $tenantId);
        $this->handleUpload((int) $id, $tenantId);

        try {
            service('erp')->onInvoiceRecorded($tenantId, (int) $id, $tenant);
        } catch (\Throwable $e) {
            return redirect()->to('/module/finance/invoices')->with('error', $e->getMessage());
        }

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
            'lines'          => model(FinInvoiceLineModel::class)->getForInvoice($id, $tenantId),
            'contacts'       => model(FinContactModel::class)->getForTenant($tenantId),
            'projects'       => model(\App\Models\ProjectModel::class)->getForTenant($tenantId),
            'files'          => model(FinInvoiceFileModel::class)->getForInvoice($id, $tenantId),
            'vatRate'        => (float) ($invoice['vat_rate'] ?? 10),
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
        model(FinInvoiceLineModel::class)->deleteForInvoice($id);
        $this->saveLines($id, $tenantId);
        $this->handleUpload($id, $tenantId);

        try {
            service('erp')->onInvoiceRecorded($tenantId, $id, $tenant);
        } catch (\Throwable $e) {
            return redirect()->to('/module/finance/invoices')->with('error', $e->getMessage());
        }

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

        model(FinInvoiceLineModel::class)->deleteForInvoice($id);
        $model->delete($id);

        return redirect()->to('/module/finance/invoices')->with('success', lang('App.deleted'));
    }

    public function pay(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.invoices')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        helper('date');
        $tenantId = (int) $tenant['id'];
        $accountId = (int) $this->request->getPost('account_id');
        $date      = parse_jalali_input((string) $this->request->getPost('pay_date')) ?? date('Y-m-d');

        try {
            service('financeTxn')->payInvoice($tenantId, $id, $accountId, $date);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to('/module/finance/invoices')->with('success', lang('Finance.invoice_paid'));
    }

    public function submitModian(int $id)
    {
        $tenant = $this->requireModule('finance');

        if ($tenant === null || ! $this->requirePermission('finance.invoices')) {
            return $tenant === null ? $this->moduleDeniedRedirect() : $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $model    = model(FinInvoiceModel::class);
        $invoice  = $model->findForTenant($id, $tenantId);

        if ($invoice === null) {
            return redirect()->back()->with('error', lang('App.not_found'));
        }

        $lines  = model(FinInvoiceLineModel::class)->getForInvoice($id, $tenantId);
        $result = service('modian')->submitInvoice($invoice, $lines, $tenant);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        $model->update($id, [
            'modian_uuid'   => $result['uuid'] ?? null,
            'modian_status' => 'sent',
        ]);

        return redirect()->back()->with('success', $result['message']);
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

    protected function saveLines(int $invoiceId, int $tenantId): void
    {
        $descriptions = $this->request->getPost('line_description') ?: [];
        $quantities   = $this->request->getPost('line_quantity') ?: [];
        $prices       = $this->request->getPost('line_unit_price') ?: [];
        $vatRates     = $this->request->getPost('line_vat_rate') ?: [];
        $lineModel    = model(FinInvoiceLineModel::class);

        foreach ($descriptions as $i => $desc) {
            if (trim((string) $desc) === '') {
                continue;
            }

            $qty   = (float) ($quantities[$i] ?? 1);
            $price = (float) ($prices[$i] ?? 0);
            $vat   = (float) ($vatRates[$i] ?? 10);
            $sub   = $qty * $price;
            $total = $sub + ($sub * $vat / 100);

            $lineModel->insert([
                'invoice_id'  => $invoiceId,
                'tenant_id'   => $tenantId,
                'description' => (string) $desc,
                'quantity'    => $qty,
                'unit_price'  => $price,
                'vat_rate'    => $vat,
                'line_total'  => $total,
            ]);
        }
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
            'status'     => 'required|in_list[draft,sent,paid,overdue,cancelled]',
            'issue_date' => 'required',
            'due_date'   => 'permit_empty',
            'contact_id' => 'permit_empty|integer',
            'project_id' => 'permit_empty|integer',
            'vat_rate'   => 'permit_empty|decimal',
        ];
    }

    protected function payload(int $tenantId): array
    {
        $issue = parse_jalali_input((string) $this->request->getPost('issue_date')) ?? (string) $this->request->getPost('issue_date');
        $due   = $this->request->getPost('due_date');
        $due   = $due ? (parse_jalali_input((string) $due) ?? (string) $due) : null;

        $subtotal = 0.0;
        $vatTotal = 0.0;
        $descriptions = $this->request->getPost('line_description') ?: [];
        $quantities   = $this->request->getPost('line_quantity') ?: [];
        $prices       = $this->request->getPost('line_unit_price') ?: [];
        $vatRates     = $this->request->getPost('line_vat_rate') ?: [];

        foreach ($descriptions as $i => $desc) {
            if (trim((string) $desc) === '') {
                continue;
            }

            $qty  = (float) ($quantities[$i] ?? 1);
            $price = (float) ($prices[$i] ?? 0);
            $vat  = (float) ($vatRates[$i] ?? 10);
            $sub  = $qty * $price;
            $subtotal += $sub;
            $vatTotal += $sub * $vat / 100;
        }

        if ($subtotal <= 0) {
            $subtotal = (float) ($this->request->getPost('amount') ?: 0);
            $vatRate  = (float) ($this->request->getPost('vat_rate') ?: 10);
            $vatTotal = $subtotal * $vatRate / 100;
        }

        $vatRate = (float) ($this->request->getPost('vat_rate') ?: 10);

        return [
            'tenant_id'   => $tenantId,
            'contact_id'  => $this->request->getPost('contact_id') ? (int) $this->request->getPost('contact_id') : null,
            'project_id'  => $this->request->getPost('project_id') ? (int) $this->request->getPost('project_id') : null,
            'number'      => (string) $this->request->getPost('number'),
            'direction'   => (string) ($this->request->getPost('direction') ?: 'sale'),
            'subtotal'    => $subtotal,
            'vat_amount'  => $vatTotal,
            'vat_rate'    => $vatRate,
            'amount'      => $subtotal + $vatTotal,
            'status'      => (string) $this->request->getPost('status'),
            'issue_date'  => $issue,
            'due_date'    => $due,
            'description' => (string) $this->request->getPost('description'),
        ];
    }
}
