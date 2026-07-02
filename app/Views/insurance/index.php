<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-insurance">
<?= $this->include('partials/breadcrumb') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc(lang('Insurance.title')) ?></h2>
    </div>
    <a href="<?= site_url('module/insurance/new') ?>" class="btn btn-primary"><?= esc(lang('Insurance.new_policy')) ?></a>
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
            <?= view('partials/empty_state', [
                'message'     => lang('Insurance.no_policies'),
                'actionUrl'   => site_url('module/insurance/new'),
                'actionLabel' => lang('Insurance.new_policy'),
            ]) ?>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('Insurance.policy_number')) ?></th>
                        <th><?= esc(lang('Insurance.provider')) ?></th>
                        <th><?= esc(lang('Insurance.type')) ?></th>
                        <th><?= esc(lang('Insurance.premium')) ?></th>
                        <th><?= esc(lang('App.status')) ?></th>
                        <th><?= esc(lang('App.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($policies as $policy): ?>
                        <tr>
                            <td><?= esc($policy['policy_number']) ?></td>
                            <td><?= esc($policy['provider']) ?></td>
                            <td><span class="tag"><?= esc(lang('Insurance.type_' . $policy['type'])) ?></span></td>
                            <td class="amount-cell"><?= esc($fmt((float) $policy['premium'])) ?></td>
                            <td><span class="badge badge-<?= esc($policy['status']) ?>"><?= esc(lang('Insurance.status_' . $policy['status'])) ?></span></td>
                            <td class="table-actions">
                                <?php if (empty($policy['finance_txn_id']) && $accounts !== []): ?>
                                <form method="post" action="<?= site_url('module/insurance/' . $policy['id'] . '/pay') ?>" class="inline-form"><?= csrf_field() ?>
                                    <select name="account_id" class="input-sm">
                                        <?php foreach ($accounts as $acc): ?><option value="<?= $acc['id'] ?>"><?= esc($acc['name']) ?></option><?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm"><?= esc(lang('Insurance.mark_paid')) ?></button>
                                </form>
                                <?php endif; ?>
                                <a href="<?= site_url('module/insurance/' . $policy['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
                                <?= view('partials/delete_form', ['action' => site_url('module/insurance/' . $policy['id'] . '/delete')]) ?>
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
