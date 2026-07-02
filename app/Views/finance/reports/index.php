<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header card-header-row">
    <h2 class="page-heading"><?= esc(lang('Finance.reports')) ?></h2>
    <div>
        <a href="<?= site_url('module/finance/reports/export/transactions') ?>" class="btn btn-secondary btn-sm"><?= esc(lang('Finance.export_transactions')) ?></a>
        <a href="<?= site_url('module/finance/reports/export/journal') ?>" class="btn btn-secondary btn-sm"><?= esc(lang('Finance.export_journal')) ?></a>
    </div>
</div>
<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Finance.month_income')) ?></span><span class="kpi-value text-success"><?= esc($fmt($monthSummary['income'] ?? 0)) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Finance.month_expense')) ?></span><span class="kpi-value text-error"><?= esc($fmt($monthSummary['expense'] ?? 0)) ?></span></div>
</div>

<div class="content-grid" style="margin-top:20px">
    <div class="card card-elevated">
        <div class="card-header"><h3><?= esc(lang('Finance.journal')) ?></h3></div>
        <div class="table-wrap"><table class="data-table data-table-compact">
            <thead><tr><th><?= esc(lang('Finance.date')) ?></th><th><?= esc(lang('Finance.reference')) ?></th><th><?= esc(lang('Finance.description')) ?></th></tr></thead>
            <tbody><?php foreach ($journal as $entry): ?><tr><td><?= esc(jalali_date($entry['entry_date'])) ?></td><td><?= esc($entry['reference'] ?? '') ?></td><td><?= esc($entry['description'] ?? '') ?></td></tr><?php endforeach; ?></tbody>
        </table></div>
    </div>
    <div class="card card-elevated">
        <div class="card-header"><h3><?= esc(lang('Finance.trial_balance')) ?></h3></div>
        <div class="table-wrap"><table class="data-table data-table-compact">
            <thead><tr><th><?= esc(lang('Finance.account_code')) ?></th><th><?= esc(lang('Finance.account')) ?></th><th><?= esc(lang('Finance.balance')) ?></th></tr></thead>
            <tbody><?php foreach ($trialBalance as $row): ?><tr><td><?= esc($row['account_code']) ?></td><td><?= esc($row['account_name']) ?></td><td><?= esc($fmt((float) $row['balance'])) ?></td></tr><?php endforeach; ?></tbody>
        </table></div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
