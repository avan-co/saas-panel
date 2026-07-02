<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="platform-page">
    <div class="kpi-grid kpi-grid-1">
        <div class="kpi-card">
            <span class="kpi-label"><?= esc(lang('Platform.total_tenants')) ?></span>
            <span class="kpi-value"><?= count($tenants) ?></span>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><?= esc(lang('Platform.tenants')) ?></h3>
        </div>
        <div class="card-body table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('Platform.tenant_name')) ?></th>
                        <th><?= esc(lang('Platform.owner')) ?></th>
                        <th><?= esc(lang('Platform.plan')) ?></th>
                        <th><?= esc(lang('App.status')) ?></th>
                        <th><?= esc(lang('Platform.modules')) ?></th>
                        <th><?= esc(lang('App.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenants as $tenant): ?>
                        <tr>
                            <td><strong><?= esc($tenant['name']) ?></strong></td>
                            <td>
                                <?= esc($tenant['owner_name']) ?><br>
                                <small><?= esc($tenant['owner_email']) ?></small>
                            </td>
                            <td><?= esc($tenant['plan']) ?></td>
                            <td><span class="badge badge-<?= esc($tenant['status']) ?>"><?= esc(lang('App.' . $tenant['status'])) ?></span></td>
                            <td>
                                <?php foreach ($tenant['modules'] as $mod): ?>
                                    <span class="module-tag"><?= esc(lang('App.modules.' . $mod['code'])) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td class="table-actions">
                                <a href="<?= site_url('platform/tenants/' . $tenant['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
