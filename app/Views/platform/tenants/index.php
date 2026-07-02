<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-platform">
<?= $this->include('partials/module_subnav') ?>

<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Platform.total_tenants')) ?></span><span class="kpi-value"><?= count($tenants) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('App.active')) ?></span><span class="kpi-value"><?= (int) ($stats['active'] ?? 0) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('App.trial')) ?></span><span class="kpi-value"><?= (int) ($stats['trial'] ?? 0) ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('App.suspended')) ?></span><span class="kpi-value"><?= (int) ($stats['suspended'] ?? 0) ?></span></div>
</div>

<div class="page-header">
    <h2 class="page-heading"><?= esc(lang('Platform.tenants')) ?></h2>
    <a href="<?= site_url('platform/tenants/new') ?>" class="btn btn-primary"><?= esc(lang('Platform.create_tenant')) ?></a>
</div>

<div class="card">
    <div class="card-body table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= esc(lang('Platform.tenant_name')) ?></th>
                    <th><?= esc(lang('Platform.owner')) ?></th>
                    <th><?= esc(lang('Platform.plan')) ?></th>
                    <th><?= esc(lang('App.status')) ?></th>
                    <th><?= esc(lang('Platform.members')) ?></th>
                    <th><?= esc(lang('Platform.joined_at')) ?></th>
                    <th><?= esc(lang('Platform.subscription')) ?></th>
                    <th><?= esc(lang('Platform.last_login')) ?></th>
                    <th><?= esc(lang('App.actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $tenant): ?>
                    <tr>
                        <td><a href="<?= site_url('platform/tenants/' . $tenant['id']) ?>"><strong><?= esc($tenant['name']) ?></strong></a><br><small><?= esc($tenant['slug']) ?></small></td>
                        <td><?= esc($tenant['owner_name']) ?><br><small dir="ltr"><?= esc($tenant['owner_email']) ?></small></td>
                        <td><?= esc($tenant['plan']) ?></td>
                        <td><span class="badge badge-<?= esc($tenant['status']) ?>"><?= esc(lang('App.' . $tenant['status'])) ?></span></td>
                        <td><?= (int) $tenant['member_count'] ?></td>
                        <td><?= esc($tenant['created_at'] ?? '—') ?></td>
                        <td>
                            <?php if (! empty($tenant['subscription_ends_at'])): ?>
                                <?= esc(substr($tenant['subscription_starts_at'] ?? '', 0, 10)) ?> → <?= esc(substr($tenant['subscription_ends_at'], 0, 10)) ?>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?= esc($tenant['owner_last_login'] ?? '—') ?></td>
                        <td class="table-actions">
                            <a href="<?= site_url('platform/tenants/' . $tenant['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
                            <form method="post" action="<?= site_url('platform/tenants/' . $tenant['id'] . '/delete') ?>" class="inline-form" onsubmit="return confirm('<?= esc(lang('Platform.delete_confirm')) ?>')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-sm btn-danger"><?= esc(lang('App.delete')) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?= $this->endSection() ?>
