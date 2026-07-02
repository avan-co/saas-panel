<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="dashboard">
    <div class="welcome-banner">
        <h2><?= esc(lang('Dashboard.welcome', ['name' => $userName ?? ''])) ?></h2>
        <?php if ($tenant): ?>
            <p><?= esc($tenant['name']) ?> — <span class="badge"><?= esc(lang('App.' . $tenant['status'])) ?></span></p>
        <?php endif; ?>
    </div>

    <div class="kpi-grid">
        <?php
        $labels = [
            'revenue' => lang('Dashboard.kpi_revenue'),
            'expense' => lang('Dashboard.kpi_expense'),
            'payroll' => lang('Dashboard.kpi_payroll'),
            'tax'     => lang('Dashboard.kpi_tax'),
        ];
        foreach ($kpis as $kpi):
        ?>
            <div class="kpi-card">
                <span class="kpi-label"><?= esc($labels[$kpi['key']]) ?></span>
                <span class="kpi-value"><?= esc($kpi['value']) ?></span>
                <span class="kpi-change <?= $kpi['positive'] ? 'positive' : 'negative' ?>"><?= esc($kpi['change']) ?></span>
            </div>
        <?php endforeach; ?>
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
<?= $this->endSection() ?>
