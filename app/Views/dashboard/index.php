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
        foreach ($kpis as $kpi):
        ?>
            <div class="kpi-card">
                <span class="kpi-label"><?= esc($labels[$kpi['key']]) ?></span>
                <span class="kpi-value"><?= esc($kpi['value']) ?></span>
                <?php if (! empty($kpi['hint'])): ?>
                    <span class="kpi-meta"><?= esc($kpi['hint']) ?></span>
                <?php endif; ?>
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
                    <?php if ($module['code'] === 'dashboard') {
                        continue;
                    } ?>
                    <a href="<?= module_url($module['code']) ?>" class="quick-action-card">
                        <span class="quick-action-title"><?= esc(lang('App.menu.' . $module['code'])) ?></span>
                        <span class="quick-action-link"><?= esc(lang('Dashboard.open_module')) ?> →</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header card-header-row">
                <h3><?= esc(lang('Dashboard.recent')) ?></h3>
                <?php if ($recentActivity !== []): ?>
                    <a href="<?= site_url('module/finance/transactions') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('Finance.view_all')) ?></a>
                <?php endif; ?>
            </div>
            <?php if ($recentActivity === []): ?>
                <div class="card-body">
                    <?php
                    $hasFinance = false;
                    foreach ($tenantModules as $mod) {
                        if ($mod['code'] === 'finance') {
                            $hasFinance = true;
                            break;
                        }
                    }
                    if ($hasFinance):
                        echo view('partials/empty_state', [
                            'message'     => lang('Dashboard.no_activity'),
                            'actionUrl'   => site_url('module/finance/transactions/new'),
                            'actionLabel' => lang('Finance.new_transaction'),
                        ]);
                    else:
                    ?>
                        <div class="empty-state"><p><?= esc(lang('Dashboard.no_activity')) ?></p></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table data-table-compact">
                        <tbody>
                            <?php foreach ($recentActivity as $txn): ?>
                                <tr>
                                    <td class="text-muted"><?= esc($txn['txn_date']) ?></td>
                                    <td><?= esc($txn['description'] ?? '—') ?></td>
                                    <td class="amount-cell <?= $txn['type'] === 'income' ? 'positive' : 'negative' ?>">
                                        <?= $txn['type'] === 'income' ? '+' : '−' ?><?= esc(format_amount((float) $txn['amount'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
