<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-payroll">
<?= $this->include('partials/breadcrumb') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc(lang('Payroll.title')) ?></h2>
    </div>
    <a href="<?= site_url('module/payroll/employees/new') ?>" class="btn btn-primary"><?= esc(lang('Payroll.new_employee')) ?></a>
    <a href="<?= site_url('module/payroll/runs') ?>" class="btn btn-secondary"><?= esc(lang('Payroll.payroll_runs')) ?></a>
</div>

<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>

<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card kpi-card-accent">
        <span class="kpi-label"><?= esc(lang('Payroll.active_staff')) ?></span>
        <span class="kpi-value"><?= esc($activeCount) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Payroll.monthly_total')) ?></span>
        <span class="kpi-value"><?= esc($fmt($totalSalary)) ?></span>
    </div>
</div>

<div class="content-grid">
    <div class="card card-elevated">
        <div class="card-header"><h3><?= esc(lang('Payroll.employees')) ?></h3></div>
        <div class="table-wrap">
            <?php if ($employees === []): ?>
                <?= view('partials/empty_state', [
                    'message'     => lang('Payroll.no_employees'),
                    'actionUrl'   => site_url('module/payroll/employees/new'),
                    'actionLabel' => lang('Payroll.new_employee'),
                ]) ?>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= esc(lang('Payroll.name')) ?></th>
                            <th><?= esc(lang('Payroll.job_title')) ?></th>
                            <th><?= esc(lang('Payroll.base_salary')) ?></th>
                            <th><?= esc(lang('App.actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><?= esc($emp['name']) ?></td>
                                <td class="text-muted"><?= esc($emp['job_title'] ?? '—') ?></td>
                                <td class="amount-cell"><?= esc($fmt((float) $emp['base_salary'])) ?></td>
                                <td class="table-actions">
                                    <a href="<?= site_url('module/payroll/employees/' . $emp['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
                                    <?= view('partials/delete_form', ['action' => site_url('module/payroll/employees/' . $emp['id'] . '/delete')]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3><?= esc(lang('Payroll.payroll_runs')) ?></h3></div>
        <div class="table-wrap">
            <?php if ($runs === []): ?>
                <div class="empty-state"><?= esc(lang('Payroll.no_runs')) ?></div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= esc(lang('Payroll.period')) ?></th>
                            <th><?= esc(lang('Payroll.amount')) ?></th>
                            <th><?= esc(lang('Payroll.status')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($runs as $run): ?>
                            <tr>
                                <td><?= esc($run['period_year'] . '/' . str_pad((string) $run['period_month'], 2, '0', STR_PAD_LEFT)) ?></td>
                                <td class="amount-cell"><?= esc($fmt((float) $run['total_amount'])) ?></td>
                                <td><span class="badge badge-<?= esc($run['status']) ?>"><?= esc(lang('Payroll.status_' . $run['status'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
