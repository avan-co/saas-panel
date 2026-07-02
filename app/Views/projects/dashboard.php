<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/project_subnav') ?>
<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
<div class="page-header card-header-row">
    <div>
        <h2 class="page-heading"><?= esc($project['name']) ?></h2>
        <span class="badge health-<?= esc($dash['health']) ?>"><?= esc(lang('Projects.health_' . $dash['health'])) ?></span>
        <span class="badge badge-<?= esc($project['status']) ?>"><?= esc(lang('Projects.status_' . $project['status'])) ?></span>
    </div>
    <a href="<?= site_url('module/projects/' . $project['id'] . '/edit') ?>" class="btn btn-secondary"><?= esc(lang('App.edit')) ?></a>
</div>

<div class="kpi-grid kpi-grid-4">
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.progress')) ?></span><span class="kpi-value"><?= (int) $dash['progress'] ?>%</span>
        <div class="progress-bar" style="margin-top:8px"><div class="progress-fill" style="width:<?= (int) $dash['progress'] ?>%"></div></div>
    </div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.open_tasks')) ?></span><span class="kpi-value"><?= (int) $dash['task_stats']['open'] ?></span>
        <span class="kpi-meta text-error"><?= (int) $dash['task_stats']['overdue'] ?> <?= esc(lang('Projects.overdue_tasks')) ?></span>
    </div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.budget_spent')) ?></span><span class="kpi-value text-error"><?= esc($fmt($dash['budget_spent'])) ?></span>
        <span class="kpi-meta"><?= esc(lang('Projects.budget_remaining')) ?>: <?= esc($fmt($dash['budget_left'])) ?></span>
    </div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.days_remaining')) ?></span><span class="kpi-value"><?= $dash['days_left'] !== null ? (int) $dash['days_left'] : '—' ?></span></div>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Projects.open_risks')) ?></span><span class="kpi-value"><?= (int) $dash['risks_open'] ?></span></div>
    <?php if ($hasFinance): ?>
    <div class="kpi-card"><span class="kpi-label"><?= esc(lang('Finance.month_net')) ?></span><span class="kpi-value <?= $finance['profit'] >= 0 ? 'text-success' : 'text-error' ?>"><?= esc($fmt($finance['profit'])) ?></span></div>
    <?php endif; ?>
</div>

<?php if (! empty($dash['prediction']['message'])): ?>
<div class="alert alert-info" style="margin:16px 0"><?= esc(lang('Projects.ai_prediction')) ?>: <?= esc($dash['prediction']['message']) ?></div>
<?php endif; ?>

<div class="content-grid" style="margin-top:20px">
    <div class="card"><div class="card-header"><h3><?= esc(lang('Projects.recent_activity')) ?></h3></div>
    <div class="card-body">
        <?php if ($dash['recent_tasks'] === []): ?><p class="text-muted">—</p>
        <?php else: foreach ($dash['recent_tasks'] as $t): ?>
        <div style="padding:6px 0;border-bottom:1px solid var(--border)">
            <a href="<?= site_url('module/projects/' . $project['id'] . '/tasks/' . $t['id']) ?>"><?= esc($t['title']) ?></a>
            <span class="badge"><?= esc(lang('Projects.status_' . ($t['status'] === 'doing' ? 'doing' : $t['status']))) ?></span>
        </div>
        <?php endforeach; endif; ?>
    </div></div>

    <div class="card"><div class="card-header"><h3><?= esc(lang('Projects.team_members')) ?></h3></div>
    <div class="card-body">
        <?php if ($dash['members'] === []): ?><p class="text-muted">—</p>
        <?php else: foreach ($dash['members'] as $m): ?>
        <div><?= esc($m['name']) ?> <span class="text-muted">(<?= esc(lang('Projects.role_' . $m['role'])) ?>)</span></div>
        <?php endforeach; endif; ?>
    </div></div>
</div>

<?php if ($hasFinance && $transactions !== []): ?>
<div class="card" style="margin-top:20px"><div class="card-header"><h3><?= esc(lang('Finance.transactions')) ?></h3></div>
<div class="table-wrap"><table class="data-table data-table-compact"><tbody>
<?php foreach ($transactions as $txn): ?>
<tr><td><?= esc(jalali_date($txn['txn_date'])) ?></td><td><?= esc($txn['description'] ?? '') ?></td><td class="amount-cell"><?= esc($fmt((float) $txn['amount'])) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
