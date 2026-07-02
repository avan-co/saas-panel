<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/breadcrumb') ?>
<div class="page-header card-header-row">
    <h2 class="page-heading"><?= esc($project['name']) ?> — <?= esc(lang('Projects.tasks')) ?> (<?= esc($progress) ?>%)</h2>
    <a href="<?= site_url('module/projects/' . $project['id']) ?>" class="btn btn-secondary"><?= esc(lang('Projects.back_to_project')) ?></a>
</div>
<?php if ($canEdit): ?>
<div class="card form-card"><div class="card-body">
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/tasks/store') ?>" class="app-form"><?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Projects.task_title')) ?></label><input type="text" name="title" required></div>
    <div class="form-group"><label><?= esc(lang('Projects.due_date')) ?></label><input type="text" name="due_date" class="jalali-date"></div>
</div>
<button type="submit" class="btn btn-primary"><?= esc(lang('Projects.add_task')) ?></button>
</form></div></div>
<?php endif; ?>
<div class="kanban-board" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:20px">
<?php foreach (['todo' => 'Projects.status_todo', 'in_progress' => 'Projects.status_in_progress', 'done' => 'Projects.status_done'] as $key => $label): ?>
<div class="card"><div class="card-header"><h3><?= esc(lang($label)) ?></h3></div><div class="card-body">
<?php foreach ($columns[$key] as $task): ?>
<div class="kanban-card" style="padding:10px;border:1px solid var(--border);border-radius:8px;margin-bottom:8px">
<strong><?= esc($task['title']) ?></strong>
<?php if (! empty($task['due_date'])): ?><div class="text-muted"><?= esc(jalali_date($task['due_date'])) ?></div><?php endif; ?>
<?php if ($canEdit): ?>
<div style="margin-top:8px">
<?php foreach (['todo','in_progress','done'] as $st): if ($st === $task['status']) continue; ?>
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/tasks/' . $task['id'] . '/status') ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $st ?>"><button type="submit" class="btn btn-ghost btn-sm"><?= esc(lang('Projects.move_' . $st)) ?></button></form>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div></div>
<?php endforeach; ?>
</div>
</div>
<?= $this->endSection() ?>
