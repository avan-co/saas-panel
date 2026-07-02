<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header">
    <div class="page-header-text"><h2 class="page-heading"><?= esc(lang('Finance.checks')) ?></h2></div>
    <a href="<?= site_url('module/finance/checks/new') ?>" class="btn btn-primary"><?= esc(lang('Finance.new_check')) ?></a>
</div>
<div class="card card-elevated"><div class="table-wrap">
<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
<table class="data-table">
<thead><tr><th><?= esc(lang('Finance.check_number')) ?></th><th><?= esc(lang('Finance.direction')) ?></th><th><?= esc(lang('Finance.bank')) ?></th><th><?= esc(lang('Finance.due_date')) ?></th><th><?= esc(lang('Finance.amount')) ?></th><th><?= esc(lang('App.status')) ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($checks as $ch): ?>
<tr>
    <td><?= esc($ch['check_number']) ?></td>
    <td><?= esc(lang('Finance.direction_' . $ch['direction'])) ?></td>
    <td><?= esc($ch['bank'] ?? '—') ?></td>
    <td><?= esc(jalali_date($ch['due_date'])) ?></td>
    <td class="amount-cell"><?= esc($fmt((float) $ch['amount'])) ?></td>
    <td><span class="badge"><?= esc(lang('Finance.check_status_' . $ch['status'])) ?></span></td>
    <td class="table-actions">
        <a href="<?= site_url('module/finance/checks/' . $ch['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
        <?= view('partials/delete_form', ['action' => site_url('module/finance/checks/' . $ch['id'] . '/delete')]) ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
</div>
<?= $this->endSection() ?>
