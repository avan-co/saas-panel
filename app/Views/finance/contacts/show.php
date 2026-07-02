<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc($contact['name']) ?></h2></div>
<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
<div class="kpi-grid kpi-grid-4" style="margin-bottom:20px">
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Finance.contact_type')) ?></span><span class="kpi-value"><?= esc(lang('Finance.contact_type_' . $contact['type'])) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Finance.balance')) ?></span><span class="kpi-value"><?= esc($fmt((float) $contact['balance'])) ?></span></div>
</div>
<div class="card"><div class="card-header"><h3><?= esc(lang('Finance.transactions')) ?></h3></div>
<div class="table-wrap">
    <?php if ($transactions === []): ?>
        <div class="empty-state"><p><?= esc(lang('Finance.no_transactions')) ?></p></div>
    <?php else: ?>
        <table class="data-table data-table-compact">
            <thead><tr><th><?= esc(lang('Finance.date')) ?></th><th><?= esc(lang('Finance.description')) ?></th><th><?= esc(lang('Finance.amount')) ?></th></tr></thead>
            <tbody>
                <?php foreach ($transactions as $txn): ?>
                    <tr>
                        <td><?= esc(jalali_date($txn['txn_date'])) ?></td>
                        <td><?= esc($txn['description'] ?? '—') ?></td>
                        <td class="amount-cell"><?= esc($fmt((float) $txn['amount'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div></div>
</div>
<?= $this->endSection() ?>
