<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/breadcrumb') ?>
<div class="page-header">
    <div class="page-header-text"><h2 class="page-heading"><?= esc($project['name']) ?></h2><p class="page-subheading"><?= esc($project['code']) ?> — <?= esc($project['client_name'] ?? '') ?></p></div>
    <a href="<?= site_url('module/projects/' . $project['id'] . '/edit') ?>" class="btn btn-secondary"><?= esc(lang('App.edit')) ?></a>
    <a href="<?= site_url('module/projects/' . $project['id'] . '/timesheets') ?>" class="btn btn-secondary"><?= esc(lang('Projects.timesheets')) ?></a>
    <a href="<?= site_url('module/projects/' . $project['id'] . '/tasks') ?>" class="btn btn-primary"><?= esc(lang('Projects.tasks')) ?></a>
</div>
<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
<?php $budget = (float) $project['budget']; $actual = (float) ($project['actual_cost'] ?? 0); ?>
<div class="kpi-grid kpi-grid-4" style="margin-bottom:20px">
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.budget')) ?></span><span class="kpi-value"><?= esc($fmt($budget)) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.actual_cost')) ?></span><span class="kpi-value text-error"><?= esc($fmt($actual)) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.budget_remaining')) ?></span><span class="kpi-value"><?= esc($fmt(max(0, $budget - $actual))) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.progress')) ?></span><span class="kpi-value"><?= (int) $project['progress'] ?>%</span></div>
    <?php if ($hasFinance): ?>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.labor_cost')) ?></span><span class="kpi-value"><?= esc($fmt((float) ($project['labor_cost'] ?? 0))) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Finance.month_net')) ?></span><span class="kpi-value <?= $finance['profit'] >= 0 ? 'text-success' : 'text-error' ?>"><?= esc($fmt($finance['profit'])) ?></span></div>
    <?php endif; ?>
</div>
<?php if ($hasFinance): ?>
<div class="card"><div class="card-header"><h3><?= esc(lang('Finance.transactions')) ?></h3></div>
<div class="table-wrap">
    <?php if ($transactions === []): ?><div class="empty-state"><p><?= esc(lang('Finance.no_transactions')) ?></p></div>
    <?php else: ?>
    <table class="data-table data-table-compact"><thead><tr><th><?= esc(lang('Finance.date')) ?></th><th><?= esc(lang('Finance.description')) ?></th><th><?= esc(lang('Finance.amount')) ?></th></tr></thead><tbody>
        <?php foreach ($transactions as $txn): ?>
        <tr><td><?= esc(jalali_date($txn['txn_date'])) ?></td><td><?= esc($txn['description'] ?? '—') ?></td><td class="amount-cell"><?= esc($fmt((float) $txn['amount'])) ?></td></tr>
        <?php endforeach; ?>
    </tbody></table>
    <?php endif; ?>
</div></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
