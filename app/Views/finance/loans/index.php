<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header">
    <div class="page-header-text"><h2 class="page-heading"><?= esc(lang('Finance.loans')) ?></h2></div>
    <a href="<?= site_url('module/finance/loans/new') ?>" class="btn btn-primary"><?= esc(lang('Finance.new_loan')) ?></a>
</div>
<div class="card card-elevated"><div class="table-wrap">
<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
<table class="data-table">
<thead><tr><th><?= esc(lang('Finance.bank')) ?></th><th><?= esc(lang('Finance.principal')) ?></th><th><?= esc(lang('Finance.installments')) ?></th><th><?= esc(lang('Finance.remaining')) ?></th><th><?= esc(lang('App.status')) ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($loans as $loan): ?>
<tr>
    <td><?= esc($loan['bank']) ?></td>
    <td class="amount-cell"><?= esc($fmt((float) $loan['principal'])) ?></td>
    <td><?= (int) $loan['paid_installments'] ?> / <?= (int) $loan['total_installments'] ?></td>
    <td class="amount-cell"><?= esc($fmt((float) ($loan['remaining'] ?? 0))) ?></td>
    <td><span class="badge badge-<?= esc($loan['status']) ?>"><?= esc(lang('Finance.loan_status_' . $loan['status'])) ?></span></td>
    <td class="table-actions">
        <a href="<?= site_url('module/finance/loans/' . $loan['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
        <?php if ($loan['status'] === 'active'): ?>
        <form method="post" action="<?= site_url('module/finance/loans/' . $loan['id'] . '/installment') ?>" class="inline-form"><?= csrf_field() ?><button type="submit" class="btn btn-ghost btn-sm"><?= esc(lang('Finance.pay_installment')) ?></button></form>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
</div>
<?= $this->endSection() ?>
