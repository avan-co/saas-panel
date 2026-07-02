<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-tax">
<?= $this->include('partials/breadcrumb') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc(lang('Tax.title')) ?></h2>
    </div>
    <a href="<?= site_url('module/tax/new') ?>" class="btn btn-primary"><?= esc(lang('Tax.new_period')) ?></a>
</div>

<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>

<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card kpi-card-accent">
        <span class="kpi-label"><?= esc(lang('Tax.pending_tax')) ?></span>
        <span class="kpi-value text-error"><?= esc($fmt($pendingAmount)) ?></span>
    </div>
</div>

<div class="card card-elevated">
    <div class="card-header"><h3><?= esc(lang('Tax.periods')) ?></h3></div>
    <div class="table-wrap">
        <?php if ($periods === []): ?>
            <?= view('partials/empty_state', [
                'message'     => lang('Tax.no_periods'),
                'actionUrl'   => site_url('module/tax/new'),
                'actionLabel' => lang('Tax.new_period'),
            ]) ?>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('Tax.quarter')) ?></th>
                        <th><?= esc(lang('Tax.taxable_income')) ?></th>
                        <th><?= esc(lang('Tax.tax_amount')) ?></th>
                        <th><?= esc(lang('Tax.due_date')) ?></th>
                        <th><?= esc(lang('App.status')) ?></th>
                        <th><?= esc(lang('App.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($periods as $period): ?>
                        <tr>
                            <td><?= esc($period['period_year'] . ' Q' . $period['period_quarter']) ?></td>
                            <td class="amount-cell"><?= esc($fmt((float) $period['taxable_income'])) ?></td>
                            <td class="amount-cell negative"><?= esc($fmt((float) $period['tax_amount'])) ?></td>
                            <td class="text-muted"><?= esc($period['due_date'] ?? '—') ?></td>
                            <td><span class="badge badge-<?= esc($period['status']) ?>"><?= esc(lang('Tax.status_' . $period['status'])) ?></span></td>
                            <td class="table-actions">
                                <a href="<?= site_url('module/tax/' . $period['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
                                <?= view('partials/delete_form', ['action' => site_url('module/tax/' . $period['id'] . '/delete')]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</div>
<?= $this->endSection() ?>
