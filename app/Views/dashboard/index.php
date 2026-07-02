<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="dashboard">
    <div class="page-header page-header-dashboard">
        <div class="page-header-text">
            <h2 class="page-heading"><?= esc(lang('Dashboard.welcome', ['name' => $userName ?? ''])) ?></h2>
            <?php if ($tenant): ?>
                <p class="page-subheading">
                    <?= esc($tenant['name']) ?>
                    <span class="badge badge-<?= esc($tenant['status']) ?>"><?= esc(lang('App.' . $tenant['status'])) ?></span>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="kpi-grid kpi-grid-4">
        <?php
        $labels = [
            'revenue' => lang('Dashboard.kpi_revenue'),
            'expense' => lang('Dashboard.kpi_expense'),
            'payroll' => lang('Dashboard.kpi_payroll'),
            'tax'     => lang('Dashboard.kpi_tax'),
        ];
        $icons = ['revenue' => 'income', 'expense' => 'expense', 'payroll' => 'users', 'tax' => 'file'];
        foreach ($kpis as $kpi):
        ?>
            <div class="kpi-card">
                <span class="kpi-label"><?= esc($labels[$kpi['key']]) ?></span>
                <span class="kpi-value"><?= esc($kpi['value']) ?></span>
                <span class="kpi-change <?= $kpi['positive'] ? 'positive' : 'negative' ?>"><?= esc($kpi['change']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="content-grid">
        <div class="card card-elevated">
            <div class="card-header">
                <h3><?= esc(lang('Dashboard.quick_actions')) ?></h3>
            </div>
            <div class="card-body quick-actions">
                <?php foreach ($tenantModules as $module): ?>
                    <?php if ($module['code'] === 'dashboard') continue; ?>
                    <?php $url = site_url('module/' . $module['code']); ?>
                    <a href="<?= $url ?>" class="quick-action-card">
                        <span class="quick-action-title"><?= esc(lang('App.menu.' . $module['code'])) ?></span>
                        <span class="quick-action-link"><?= esc(lang('Dashboard.open_module')) ?> →</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><?= esc(lang('Dashboard.recent')) ?></h3>
            </div>
            <div class="card-body empty-state">
                <p><?= esc(lang('Dashboard.no_activity')) ?></p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
