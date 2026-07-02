<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?= $this->include('partials/breadcrumb') ?>

<?php
$moduleTabs = [
    ['key' => 'overview', 'label' => lang('Finance.overview'), 'url' => site_url('module/finance')],
    ['key' => 'transactions', 'label' => lang('Finance.transactions'), 'url' => site_url('module/finance/transactions')],
];
echo $this->include('partials/module_tabs');
?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc(lang('Finance.transactions')) ?></h2>
    </div>
    <a href="<?= site_url('module/finance/transactions/new') ?>" class="btn btn-primary"><?= esc(lang('Finance.new_transaction')) ?></a>
</div>

<div class="card card-elevated">
    <div class="table-wrap">
        <?php if ($transactions === []): ?>
            <div class="empty-state"><?= esc(lang('Finance.no_transactions')) ?></div>
        <?php else: ?>
            <?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('Finance.date')) ?></th>
                        <th><?= esc(lang('Finance.description')) ?></th>
                        <th><?= esc(lang('Finance.account')) ?></th>
                        <th><?= esc(lang('Finance.category')) ?></th>
                        <th><?= esc(lang('Finance.amount')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $txn): ?>
                        <tr>
                            <td class="text-muted"><?= esc($txn['txn_date']) ?></td>
                            <td><?= esc($txn['description'] ?? '—') ?></td>
                            <td><?= esc($txn['account_name'] ?? '—') ?></td>
                            <td>
                                <?php if (! empty($txn['category_name'])): ?>
                                    <span class="tag" style="--tag-color: <?= esc($txn['category_color'] ?? '#64748b') ?>">
                                        <?= esc($txn['category_name']) ?>
                                    </span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="amount-cell <?= $txn['type'] === 'income' ? 'positive' : 'negative' ?>">
                                <?= $txn['type'] === 'income' ? '+' : '−' ?><?= esc($fmt((float) $txn['amount'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
