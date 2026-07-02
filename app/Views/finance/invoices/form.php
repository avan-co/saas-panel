<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc($invoice ? lang('Finance.edit_invoice') : lang('Finance.new_invoice')) ?></h2></div>
<div class="card form-card"><div class="card-body">
<form method="post" enctype="multipart/form-data" action="<?= $invoice ? site_url('module/finance/invoices/' . $invoice['id'] . '/update') : site_url('module/finance/invoices/store') ?>" class="app-form" id="invoiceForm">
<?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.invoice_number')) ?></label><input type="text" name="number" value="<?= esc(old('number', $invoice['number'] ?? '')) ?>" required></div>
    <div class="form-group"><label><?= esc(lang('Finance.vat_rate')) ?></label><input type="number" name="vat_rate" min="0" max="100" step="0.1" value="<?= esc(old('vat_rate', $vatRate ?? 10)) ?>"></div>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.contact')) ?></label>
        <select name="contact_id"><option value="">—</option>
            <?php foreach ($contacts as $c): ?><option value="<?= $c['id'] ?>" <?= (string) old('contact_id', $invoice['contact_id'] ?? '') === (string) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <?php if (! empty($projects)): ?>
    <div class="form-group"><label><?= esc(lang('Finance.project')) ?></label>
        <select name="project_id"><option value="">—</option>
            <?php foreach ($projects as $p): ?><option value="<?= $p['id'] ?>" <?= (string) old('project_id', $invoice['project_id'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.issue_date')) ?></label><input type="text" name="issue_date" class="jalali-date" value="<?= esc(old('issue_date', isset($invoice) ? jalali_date($invoice['issue_date']) : today_for_input($locale ?? 'fa'))) ?>" required></div>
    <div class="form-group"><label><?= esc(lang('Finance.due_date')) ?></label><input type="text" name="due_date" class="jalali-date" value="<?= esc(old('due_date', ! empty($invoice['due_date']) ? jalali_date($invoice['due_date']) : '')) ?>"></div>
</div>
<h3><?= esc(lang('Finance.line_items')) ?></h3>
<div id="lineItems">
<?php $lineRows = $lines !== [] ? $lines : [['description'=>'','quantity'=>1,'unit_price'=>0,'vat_rate'=>10]]; ?>
<?php foreach ($lineRows as $i => $line): ?>
<div class="form-row line-row">
    <div class="form-group"><label><?= esc(lang('Finance.description')) ?></label><input type="text" name="line_description[]" value="<?= esc($line['description'] ?? '') ?>"></div>
    <div class="form-group"><label><?= esc(lang('Finance.quantity')) ?></label><input type="number" name="line_quantity[]" min="0" step="0.01" value="<?= esc($line['quantity'] ?? 1) ?>"></div>
    <div class="form-group"><label><?= esc(lang('Finance.unit_price')) ?></label><input type="number" name="line_unit_price[]" min="0" value="<?= esc($line['unit_price'] ?? 0) ?>"></div>
    <div class="form-group"><label><?= esc(lang('Finance.vat_rate')) ?>%</label><input type="number" name="line_vat_rate[]" min="0" value="<?= esc($line['vat_rate'] ?? 10) ?>"></div>
</div>
<?php endforeach; ?>
</div>
<button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('lineItems').insertAdjacentHTML('beforeend', document.querySelector('.line-row').outerHTML)"><?= esc(lang('Finance.add_line')) ?></button>
<?php if ($invoice): ?>
<div class="form-row" style="margin-top:12px">
    <div class="form-group"><strong><?= esc(lang('Finance.subtotal')) ?>:</strong> <?= esc(number_format((float)($invoice['subtotal']??0),0)) ?></div>
    <div class="form-group"><strong><?= esc(lang('Finance.vat_amount')) ?>:</strong> <?= esc(number_format((float)($invoice['vat_amount']??0),0)) ?></div>
    <div class="form-group"><strong><?= esc(lang('Finance.total')) ?>:</strong> <?= esc(number_format((float)($invoice['amount']??0),0)) ?></div>
</div>
<?php endif; ?>
<div class="form-group"><label><?= esc(lang('App.status')) ?></label>
    <select name="status"><?php foreach (['draft','sent','paid','overdue','cancelled'] as $s): ?><option value="<?= $s ?>" <?= old('status', $invoice['status'] ?? 'draft') === $s ? 'selected' : '' ?>><?= esc(lang('Finance.invoice_status_' . $s)) ?></option><?php endforeach; ?></select>
</div>
<div class="form-group"><label><?= esc(lang('Finance.attachment')) ?></label><input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp"></div>
<?php if (! empty($files)): ?>
<div class="form-group"><?php foreach ($files as $f): ?><a href="<?= site_url('module/finance/invoices/files/' . $f['id'] . '/download') ?>" class="btn btn-ghost btn-sm"><?= esc($f['original_name']) ?></a> <?php endforeach; ?></div>
<?php endif; ?>
<div class="form-actions"><a href="<?= site_url('module/finance/invoices') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a><button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button></div>
</form></div></div>
</div>
<?= $this->endSection() ?>
