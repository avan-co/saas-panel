<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/project_subnav') ?>
<h2 class="page-heading"><?= esc(lang('Projects.calendar')) ?></h2>
<div class="card"><div class="card-body">
<?php if ($events === []): ?><p class="text-muted">—</p>
<?php else: foreach ($events as $ev): ?>
<div style="padding:8px 0;border-bottom:1px solid var(--border)">
    <span class="badge"><?= esc($ev['type']) ?></span>
    <strong><?= esc(jalali_date($ev['date'])) ?></strong> — <?= esc($ev['title']) ?>
</div>
<?php endforeach; endif; ?>
</div></div>
</div>
<?= $this->endSection() ?>
