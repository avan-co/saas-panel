<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc(lang('Finance.ceo_dashboard')) ?></h2>
        <p class="page-subheading"><?= esc(lang('Finance.ceo_subtitle')) ?></p>
    </div>
    <a href="<?= site_url('module/finance/transactions/new') ?>" class="btn btn-primary"><?= esc(lang('Finance.quick_txn')) ?></a>
</div>

<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>

<div class="kpi-grid kpi-grid-4 ceo-kpi-grid">
    <div class="kpi-card kpi-card-accent">
        <span class="kpi-label"><?= esc(lang('Finance.total_liquidity')) ?></span>
        <span class="kpi-value"><?= esc($fmt($totalBalance)) ?></span>
        <span class="kpi-meta"><?= esc($currency) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.bank_balance')) ?></span>
        <span class="kpi-value"><?= esc($fmt($bankBalance)) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.cash_balance')) ?></span>
        <span class="kpi-value"><?= esc($fmt($cashBalance)) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.month_net')) ?></span>
        <span class="kpi-value <?= $monthNet >= 0 ? 'text-success' : 'text-error' ?>"><?= esc($fmt($monthNet)) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.month_income')) ?></span>
        <span class="kpi-value text-success"><?= esc($fmt($monthIncome)) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.month_expense')) ?></span>
        <span class="kpi-value text-error"><?= esc($fmt($monthExpense)) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.burn_rate')) ?></span>
        <span class="kpi-value"><?= esc($fmt($burnRate)) ?></span>
        <span class="kpi-meta"><?= esc(lang('Finance.per_month')) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.runway')) ?></span>
        <span class="kpi-value"><?= $runwayMonths !== null ? esc($runwayMonths . ' ' . lang('Finance.months')) : '—' ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.pending_tax')) ?></span>
        <span class="kpi-value text-error"><?= esc($fmt($pendingTax)) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.due_today')) ?></span>
        <span class="kpi-value"><?= esc($fmt($dueToday)) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Finance.overdue_payments')) ?></span>
        <span class="kpi-value <?= $overduePayments > 0 ? 'text-error' : '' ?>"><?= esc($overduePayments) ?></span>
    </div>
</div>

<?php if ($budgetAlerts !== []): ?>
<div class="alert alert-warning budget-alerts">
    <strong><?= esc(lang('Finance.budget_alerts')) ?>:</strong>
    <?php foreach ($budgetAlerts as $alert): ?>
        <div><?= esc($alert['category']) ?> — <?= esc($alert['percent']) ?>% (<?= esc($fmt($alert['spent'])) ?> / <?= esc($fmt($alert['budget'])) ?>)</div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="content-grid">
    <div class="card card-elevated">
        <div class="card-header"><h3><?= esc(lang('Finance.cashflow_chart')) ?></h3></div>
        <div class="card-body cashflow-chart">
            <?php if ($cashflow === []): ?>
                <div class="empty-state"><p><?= esc(lang('Finance.no_transactions')) ?></p></div>
            <?php else: ?>
                <?php foreach ($cashflow as $date => $flow): ?>
                    <?php $netDay = ($flow['income'] ?? 0) - ($flow['expense'] ?? 0); ?>
                    <div class="cashflow-row">
                        <span class="cashflow-date"><?= esc(jalali_date($date)) ?></span>
                        <div class="cashflow-bar-wrap">
                            <div class="cashflow-bar <?= $netDay >= 0 ? 'positive' : 'negative' ?>" style="width: <?= min(100, abs($netDay) / max(1, $monthExpense) * 100) ?>%"></div>
                        </div>
                        <span class="cashflow-value <?= $netDay >= 0 ? 'text-success' : 'text-error' ?>"><?= esc($fmt($netDay)) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-row">
            <h3><?= esc(lang('Finance.accounts_card')) ?></h3>
            <a href="<?= site_url('module/finance/accounts') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('Finance.manage_accounts')) ?></a>
        </div>
        <div class="card-body account-list">
            <?php foreach ($accounts as $account): ?>
                <div class="account-row">
                    <div class="account-info">
                        <span class="account-name"><?= esc($account['name']) ?></span>
                        <span class="account-type"><?= esc(lang('Finance.account_type_' . $account['type'])) ?></span>
                    </div>
                    <span class="account-balance"><?= esc($fmt((float) $account['balance'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card card-elevated" style="margin-top:20px">
    <div class="card-header card-header-row">
        <h3><?= esc(lang('Finance.recent_txn')) ?></h3>
        <a href="<?= site_url('module/finance/transactions') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('Finance.view_all')) ?></a>
    </div>
    <div class="table-wrap">
        <table class="data-table data-table-compact">
            <thead>
                <tr>
                    <th><?= esc(lang('Finance.date')) ?></th>
                    <th><?= esc(lang('Finance.description')) ?></th>
                    <th><?= esc(lang('Finance.amount')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentTransactions as $txn): ?>
                    <tr>
                        <td><?= esc(jalali_date($txn['txn_date'])) ?></td>
                        <td><?= esc($txn['description'] ?? '—') ?></td>
                        <td class="amount-cell <?= $txn['type'] === 'income' ? 'positive' : 'negative' ?>">
                            <?= $txn['type'] === 'income' ? '+' : '−' ?><?= esc($fmt((float) $txn['amount'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?= $this->endSection() ?>
