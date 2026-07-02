<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-payroll">
<?= $this->include('partials/breadcrumb') ?>
<div class="page-header card-header-row">
    <h2 class="page-heading"><?= esc(lang('Payroll.payroll_runs')) ?></h2>
    <a href="<?= site_url('module/payroll/runs/new') ?>" class="btn btn-primary"><?= esc(lang('Payroll.new_run')) ?></a>
</div>
<div class="table-wrap"><table class="data-table">
<thead><tr><th><?= esc(lang('Payroll.period')) ?></th><th><?= esc(lang('Payroll.amount')) ?></th><th><?= esc(lang('App.status')) ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($runs as $run): ?>
<tr>
    <td><?= esc($run['period_year']) ?>/<?= esc($run['period_month']) ?></td>
    <td><?= esc(number_format((float) $run['total_amount'], 0)) ?></td>
    <td><?= esc(lang('Payroll.status_' . $run['status'])) ?></td>
    <td><a href="<?= site_url('module/payroll/runs/' . $run['id']) ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.view')) ?></a></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
</div>
<?= $this->endSection() ?>
