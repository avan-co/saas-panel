<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header">
    <div class="page-header-text"><h2 class="page-heading"><?= esc(lang('Finance.invoices')) ?></h2></div>
    <a href="<?= site_url('module/finance/invoices/new') ?>" class="btn btn-primary"><?= esc(lang('Finance.new_invoice')) ?></a>
</div>
<div class="card card-elevated"><div class="table-wrap">
<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
<table class="data-table">
<thead><tr><th><?= esc(lang('Finance.invoice_number')) ?></th><th><?= esc(lang('Finance.contact')) ?></th><th><?= esc(lang('Finance.amount')) ?></th><th><?= esc(lang('Finance.date')) ?></th><th><?= esc(lang('App.status')) ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($invoices as $inv): ?>
<tr>
    <td><?= esc($inv['number']) ?></td>
    <td><?= esc($inv['contact_name'] ?? '—') ?></td>
    <td class="amount-cell"><?= esc($fmt((float) $inv['amount'])) ?></td>
    <td><?= esc(jalali_date($inv['issue_date'])) ?></td>
    <td><span class="badge badge-<?= esc($inv['status']) ?>"><?= esc(lang('Finance.invoice_status_' . $inv['status'])) ?></span></td>
    <td class="table-actions">
        <a href="<?= site_url('module/finance/invoices/' . $inv['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
        <?= view('partials/delete_form', ['action' => site_url('module/finance/invoices/' . $inv['id'] . '/delete')]) ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
</div>
<?= $this->endSection() ?>
