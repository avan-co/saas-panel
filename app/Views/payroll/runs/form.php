<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-payroll">
<?= $this->include('partials/breadcrumb') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Payroll.new_run')) ?></h2></div>
<div class="card form-card"><div class="card-body">
<form method="post" action="<?= site_url('module/payroll/runs/store') ?>" class="app-form"><?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Payroll.period_year')) ?></label><input type="number" name="period_year" value="<?= date('Y') ?>" required></div>
    <div class="form-group"><label><?= esc(lang('Payroll.period_month')) ?></label><input type="number" name="period_month" min="1" max="12" value="<?= date('n') ?>" required></div>
</div>
<div class="form-actions"><a href="<?= site_url('module/payroll/runs') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a><button type="submit" class="btn btn-primary"><?= esc(lang('Payroll.calculate_run')) ?></button></div>
</form></div></div>
</div>
<?= $this->endSection() ?>
