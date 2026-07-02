<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Finance.forecast')) ?></h2></div>
<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
<div class="kpi-grid kpi-grid-3">
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Finance.current_balance')) ?></span><span class="kpi-value"><?= esc($fmt($forecast['current_balance'])) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Finance.monthly_net')) ?></span><span class="kpi-value"><?= esc($fmt($forecast['monthly_net'])) ?></span></div>
</div>
<div class="card card-elevated" style="margin-top:20px"><div class="card-header"><h3><?= esc(lang('Finance.forecast_chart')) ?></h3></div>
<div class="card-body">
<?php foreach ($forecast['points'] as $pt): ?>
<div class="cashflow-row"><span><?= esc(lang('Finance.month')) ?> <?= $pt['month'] ?></span><span class="kpi-value"><?= esc($fmt($pt['balance'])) ?> <?= esc($currency) ?></span></div>
<?php endforeach; ?>
</div></div>
<div class="card form-card" style="margin-top:20px"><div class="card-body">
<h3><?= esc(lang('Finance.scenario_planning')) ?></h3>
<form method="post" action="<?= site_url('module/finance/forecast/scenario') ?>" class="app-form"><?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.scenario_name')) ?></label><input type="text" name="name" required></div>
    <div class="form-group"><label><?= esc(lang('Finance.hire_cost')) ?></label><input type="number" name="hire_cost" min="0" value="0"></div>
    <div class="form-group"><label><?= esc(lang('Finance.hire_monthly')) ?></label><input type="number" name="hire_monthly" min="0" value="0"></div>
</div>
<button type="submit" class="btn btn-primary"><?= esc(lang('Finance.run_scenario')) ?></button>
</form></div></div>
</div>
<?= $this->endSection() ?>
