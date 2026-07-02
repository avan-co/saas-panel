<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-payroll">
<?= $this->include('partials/breadcrumb') ?>
<div class="page-header card-header-row">
    <h2 class="page-heading"><?= esc(lang('Payroll.run_detail')) ?> — <?= esc($run['period_year']) ?>/<?= esc($run['period_month']) ?></h2>
    <div>
        <a href="<?= site_url('module/payroll/runs/' . $run['id'] . '/dsk') ?>" class="btn btn-secondary"><?= esc(lang('Payroll.export_dsk')) ?></a>
        <?php if ($run['status'] === 'draft'): ?>
        <form method="post" action="<?= site_url('module/payroll/runs/' . $run['id'] . '/approve') ?>" class="inline-form"><?= csrf_field() ?><button type="submit" class="btn btn-primary"><?= esc(lang('Payroll.approve_run')) ?></button></form>
        <?php endif; ?>
    </div>
</div>
<div class="table-wrap"><table class="data-table">
<thead><tr><th><?= esc(lang('Payroll.name')) ?></th><th><?= esc(lang('Payroll.base_salary')) ?></th><th><?= esc(lang('Payroll.insurance')) ?></th><th><?= esc(lang('Payroll.tax')) ?></th><th><?= esc(lang('Payroll.net_pay')) ?></th></tr></thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr>
    <td><?= esc($item['name']) ?></td>
    <td><?= esc(number_format((float) $item['base_salary'], 0)) ?></td>
    <td><?= esc(number_format((float) $item['insurance_employee'], 0)) ?></td>
    <td><?= esc(number_format((float) $item['tax_amount'], 0)) ?></td>
    <td><?= esc(number_format((float) $item['net_pay'], 0)) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
</div>
<?= $this->endSection() ?>
