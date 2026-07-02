<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Finance.reports')) ?></h2></div>
<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Finance.month_income')) ?></span><span class="kpi-value text-success"><?= esc($fmt($monthSummary['income'] ?? 0)) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Finance.month_expense')) ?></span><span class="kpi-value text-error"><?= esc($fmt($monthSummary['expense'] ?? 0)) ?></span></div>
</div>
<p class="text-muted"><?= esc(lang('Finance.reports_hint')) ?></p>
</div>
<?= $this->endSection() ?>
