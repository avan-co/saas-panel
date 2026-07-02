<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/project_subnav') ?>
<h2 class="page-heading"><?= esc(lang('Projects.reports')) ?></h2>
<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.report_progress')) ?></span><span class="kpi-value"><?= (int) $report['tasks']['done'] ?>/<?= (int) $report['tasks']['total'] ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.report_hours')) ?></span><span class="kpi-value"><?= esc($report['total_hours']) ?>h</span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.labor_cost')) ?></span><span class="kpi-value"><?= esc($fmt($report['labor_cost'])) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.overdue_tasks')) ?></span><span class="kpi-value text-error"><?= (int) $report['tasks']['overdue'] ?></span></div>
</div>
<?php if (! empty($prediction['message'])): ?>
<div class="alert alert-info"><?= esc($prediction['message']) ?></div>
<?php endif; ?>
<div class="card" style="margin-top:20px"><div class="card-header"><h3><?= esc(lang('Projects.report_performance')) ?></h3></div>
<div class="table-wrap"><table class="data-table">
<thead><tr><th><?= esc(lang('Payroll.employee')) ?></th><th><?= esc(lang('Projects.open_tasks_count')) ?></th><th><?= esc(lang('Projects.completion_rate')) ?></th><th><?= esc(lang('Projects.hours')) ?></th></tr></thead>
<tbody>
<?php foreach ($report['performance'] as $p): ?>
<tr><td><?= esc($p['name']) ?></td><td><?= (int) $p['assigned'] ?></td><td><?= (int) $p['completion'] ?>%</td><td><?= esc($p['hours']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
</div>
<?= $this->endSection() ?>
