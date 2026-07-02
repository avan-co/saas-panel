<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-insurance">
<?= $this->include('partials/breadcrumb') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc(lang('Insurance.title')) ?></h2>
    </div>
</div>

<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>

<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card kpi-card-accent">
        <span class="kpi-label"><?= esc(lang('Insurance.active_policies')) ?></span>
        <span class="kpi-value"><?= esc($activeCount) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Insurance.monthly_premium')) ?></span>
        <span class="kpi-value"><?= esc($fmt($totalPremium)) ?></span>
    </div>
</div>

<div class="card card-elevated">
    <div class="card-header"><h3><?= esc(lang('Insurance.policies')) ?></h3></div>
    <div class="table-wrap">
        <?php if ($policies === []): ?>
            <div class="empty-state"><?= esc(lang('Insurance.no_policies')) ?></div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('Insurance.policy_number')) ?></th>
                        <th><?= esc(lang('Insurance.provider')) ?></th>
                        <th><?= esc(lang('Insurance.type')) ?></th>
                        <th><?= esc(lang('Insurance.premium')) ?></th>
                        <th><?= esc(lang('Insurance.valid_until')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($policies as $policy): ?>
                        <tr>
                            <td><?= esc($policy['policy_number']) ?></td>
                            <td><?= esc($policy['provider']) ?></td>
                            <td><span class="tag"><?= esc(lang('Insurance.type_' . $policy['type'])) ?></span></td>
                            <td class="amount-cell"><?= esc($fmt((float) $policy['premium'])) ?></td>
                            <td class="text-muted"><?= esc($policy['end_date'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</div>
<?= $this->endSection() ?>
