<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
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
        <h2 class="page-heading"><?= esc(lang('Finance.title')) ?></h2>
        <p class="page-subheading"><?= esc(lang('Finance.cashflow_hint')) ?></p>
    </div>
    <a href="<?= site_url('module/finance/transactions/new') ?>" class="btn btn-primary"><?= esc(lang('Finance.new_transaction')) ?></a>
</div>

<?php
$net = ($summary['income'] ?? 0) - ($summary['expense'] ?? 0);
$fmt = static fn (float $n): string => number_format($n, 0, '.', ',');
?>

<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card kpi-card-accent">
        <span class="kpi-icon income">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </span>
        <span class="kpi-label"><?= esc(lang('Finance.total_balance')) ?></span>
        <span class="kpi-value"><?= esc($fmt($balance)) ?></span>
        <span class="kpi-meta"><?= esc($currency ?? 'IRR') ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.month_income')) ?></span>
        <span class="kpi-value text-success"><?= esc($fmt($summary['income'])) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.month_expense')) ?></span>
        <span class="kpi-value text-error"><?= esc($fmt($summary['expense'])) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.month_net')) ?></span>
        <span class="kpi-value <?= $net >= 0 ? 'text-success' : 'text-error' ?>"><?= esc($fmt($net)) ?></span>
    </div>
</div>

<div class="content-grid">
    <div class="card card-elevated">
        <div class="card-header card-header-row">
            <h3><?= esc(lang('Finance.recent_txn')) ?></h3>
            <a href="<?= site_url('module/finance/transactions') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('Finance.view_all')) ?></a>
        </div>
        <div class="table-wrap">
            <?php if ($recent === []): ?>
                <?= view('partials/empty_state', [
                    'message'     => lang('Finance.no_transactions'),
                    'actionUrl'   => site_url('module/finance/transactions/new'),
                    'actionLabel' => lang('Finance.new_transaction'),
                ]) ?>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= esc(lang('Finance.date')) ?></th>
                            <th><?= esc(lang('Finance.description')) ?></th>
                            <th><?= esc(lang('Finance.category')) ?></th>
                            <th><?= esc(lang('Finance.amount')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $txn): ?>
                            <tr>
                                <td class="text-muted"><?= esc($txn['txn_date']) ?></td>
                                <td><?= esc($txn['description'] ?? '—') ?></td>
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

    <div class="card">
        <div class="card-header">
            <h3><?= esc(lang('Finance.accounts_card')) ?></h3>
        </div>
        <div class="card-body account-list">
            <?php if ($accounts === []): ?>
                <?= view('partials/empty_state', ['message' => lang('Finance.no_accounts')]) ?>
            <?php else: ?>
                <?php foreach ($accounts as $account): ?>
                    <div class="account-row">
                        <div class="account-info">
                            <span class="account-name"><?= esc($account['name']) ?></span>
                            <span class="account-type"><?= esc($account['type']) ?></span>
                        </div>
                        <span class="account-balance"><?= esc($fmt((float) $account['balance'])) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
