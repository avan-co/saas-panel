<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc($invoice ? lang('Finance.edit_invoice') : lang('Finance.new_invoice')) ?></h2></div>
<div class="card form-card"><div class="card-body">
<form method="post" enctype="multipart/form-data" action="<?= $invoice ? site_url('module/finance/invoices/' . $invoice['id'] . '/update') : site_url('module/finance/invoices/store') ?>" class="app-form">
<?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.invoice_number')) ?></label><input type="text" name="number" value="<?= esc(old('number', $invoice['number'] ?? '')) ?>" required></div>
    <div class="form-group"><label><?= esc(lang('Finance.amount')) ?></label><input type="number" name="amount" min="0" value="<?= esc(old('amount', $invoice['amount'] ?? '')) ?>" required></div>
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
