<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/breadcrumb') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc(lang('Projects.title')) ?></h2>
    </div>
    <a href="<?= site_url('module/projects/workload') ?>" class="btn btn-secondary"><?= esc(lang('Projects.workload')) ?></a>
    <a href="<?= site_url('module/projects/new') ?>" class="btn btn-primary"><?= esc(lang('Projects.new_project')) ?></a>
</div>

<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>

<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card kpi-card-accent">
        <span class="kpi-label"><?= esc(lang('Projects.active_projects')) ?></span>
        <span class="kpi-value"><?= esc($activeCount) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Projects.total_budget')) ?></span>
        <span class="kpi-value"><?= esc($fmt($totalBudget)) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label"><?= esc(lang('Projects.avg_progress')) ?></span>
        <span class="kpi-value"><?= esc($avgProgress) ?>%</span>
    </div>
</div>

<div class="card card-elevated">
    <div class="card-header"><h3><?= esc(lang('Projects.title')) ?></h3></div>
    <div class="table-wrap">
        <?php if ($projects === []): ?>
            <?= view('partials/empty_state', [
                'message'     => lang('Projects.no_projects'),
                'actionUrl'   => site_url('module/projects/new'),
                'actionLabel' => lang('Projects.new_project'),
            ]) ?>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('Projects.project_name')) ?></th>
                        <th><?= esc(lang('Projects.client')) ?></th>
                        <th><?= esc(lang('Projects.budget')) ?></th>
                        <th><?= esc(lang('Projects.progress')) ?></th>
                        <th><?= esc(lang('App.status')) ?></th>
                        <th><?= esc(lang('App.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td>
                                <strong><?= esc($project['name']) ?></strong>
                                <div class="text-muted text-sm"><?= esc($project['code']) ?></div>
                            </td>
                            <td><?= esc($project['client_name'] ?? '—') ?></td>
                            <td class="amount-cell"><?= esc($fmt((float) $project['budget'])) ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= (int) $project['progress'] ?>%"></div>
                                </div>
                                <span class="text-muted text-sm"><?= (int) $project['progress'] ?>%</span>
                            </td>
                            <td><span class="badge badge-<?= esc($project['status']) ?>"><?= esc(lang('Projects.status_' . $project['status'])) ?></span></td>
                            <td class="table-actions">
                                <a href="<?= site_url('module/projects/' . $project['id']) ?>" class="btn btn-ghost btn-sm"><?= esc(lang('Projects.view')) ?></a>
                                <a href="<?= site_url('module/projects/' . $project['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
                                <?= view('partials/delete_form', ['action' => site_url('module/projects/' . $project['id'] . '/delete')]) ?>
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
