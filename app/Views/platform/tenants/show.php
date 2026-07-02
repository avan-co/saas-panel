<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-platform">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header">
    <h2 class="page-heading"><?= esc($tenant['name']) ?></h2>
    <a href="<?= site_url('platform/tenants/' . $tenant['id'] . '/edit') ?>" class="btn btn-primary"><?= esc(lang('App.edit')) ?></a>
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-header"><h3><?= esc(lang('Platform.tenant_info')) ?></h3></div>
        <div class="card-body">
            <dl class="detail-list">
                <dt><?= esc(lang('Platform.slug')) ?></dt><dd dir="ltr"><?= esc($tenant['slug']) ?></dd>
                <dt><?= esc(lang('Platform.plan')) ?></dt><dd><?= esc($tenant['plan']) ?></dd>
                <dt><?= esc(lang('App.status')) ?></dt><dd><span class="badge badge-<?= esc($tenant['status']) ?>"><?= esc(lang('App.' . $tenant['status'])) ?></span></dd>
                <dt><?= esc(lang('Platform.joined_at')) ?></dt><dd><?= esc($tenant['created_at'] ?? '—') ?></dd>
                <dt><?= esc(lang('Platform.subscription')) ?></dt>
                <dd><?= esc(substr($tenant['subscription_starts_at'] ?? '—', 0, 10)) ?> → <?= esc(substr($tenant['subscription_ends_at'] ?? '—', 0, 10)) ?></dd>
                <dt><?= esc(lang('Platform.owner')) ?></dt>
                <dd><?= esc($owner['name'] ?? '—') ?> (<span dir="ltr"><?= esc($owner['email'] ?? '') ?></span>)</dd>
                <dt><?= esc(lang('Platform.last_login')) ?></dt>
                <dd><?= esc($owner['last_login_at'] ?? '—') ?> <?= ! empty($owner['last_login_ip']) ? '(' . esc($owner['last_login_ip']) . ')' : '' ?></dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3><?= esc(lang('Platform.modules')) ?></h3></div>
        <div class="card-body">
            <?php foreach ($modules as $mod): ?>
                <span class="module-tag"><?= esc(lang('App.modules.' . $mod['code'])) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3><?= esc(lang('Platform.members')) ?></h3></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= esc(lang('Settings.user_name')) ?></th>
                    <th><?= esc(lang('Settings.user_email')) ?></th>
                    <th><?= esc(lang('Settings.user_role')) ?></th>
                    <th><?= esc(lang('Settings.department')) ?></th>
                    <th><?= esc(lang('Platform.last_login')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td><?= esc($m['name']) ?></td>
                    <td dir="ltr"><?= esc($m['email']) ?></td>
                    <td><span class="badge"><?= esc(lang('Settings.role_' . $m['role'])) ?></span></td>
                    <td><?= esc($m['department'] ?? '—') ?></td>
                    <td><?= esc($m['last_login_at'] ?? '—') ?> <?= ! empty($m['last_login_ip']) ? '<small dir="ltr">(' . esc($m['last_login_ip']) . ')</small>' : '' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?= $this->endSection() ?>
