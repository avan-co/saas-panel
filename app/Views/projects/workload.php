<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/breadcrumb') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Projects.workload')) ?></h2></div>
<div class="card"><div class="card-header"><h3><?= esc(lang('Projects.workload_heatmap')) ?></h3></div>
<div class="card-body">
<?php foreach ($workload as $row): ?>
<div class="workload-row" style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
    <div style="width:140px"><?= esc($row['name']) ?></div>
    <div style="flex:1;height:24px;background:var(--surface-2);border-radius:4px;overflow:hidden">
        <div style="width:<?= min(100,(int)$row['load_pct']) ?>%;height:100%;background:<?= $row['overload']?'var(--error)':'var(--primary)' ?>;opacity:0.8"></div>
    </div>
    <div style="width:80px;text-align:left"><?= (int) $row['load_pct'] ?>%</div>
    <div class="text-muted text-sm"><?= (int) $row['open_tasks'] ?> <?= esc(lang('Projects.open_tasks_count')) ?></div>
    <?php if ($row['overload']): ?><span class="badge badge-error"><?= esc(lang('Projects.overload')) ?></span><?php endif; ?>
</div>
<?php endforeach; ?>
</div></div>
</div>
<?= $this->endSection() ?>
