<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/project_subnav') ?>
<div class="page-header card-header-row">
    <h2 class="page-heading"><?= esc(lang('Projects.list_view')) ?></h2>
    <a href="<?= site_url('module/projects/' . $project['id'] . '/tasks') ?>" class="btn btn-secondary"><?= esc(lang('Projects.kanban_view')) ?></a>
</div>
<div class="card"><div class="table-wrap"><table class="data-table">
<thead><tr><th><?= esc(lang('Projects.task_title')) ?></th><th><?= esc(lang('Projects.assignee')) ?></th><th><?= esc(lang('App.status')) ?></th><th><?= esc(lang('Projects.due_date')) ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($tasks as $task): ?>
<tr>
    <td><a href="<?= site_url('module/projects/' . $project['id'] . '/tasks/' . $task['id']) ?>"><?= esc($task['title']) ?></a></td>
    <td><?= esc($task['assignee_name'] ?? '—') ?></td>
    <td><span class="badge"><?= esc($task['status']) ?></span></td>
    <td><?= ! empty($task['due_date']) ? esc(jalali_date($task['due_date'])) : '—' ?></td>
    <td><a href="<?= site_url('module/projects/' . $project['id'] . '/tasks/' . $task['id']) ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.view')) ?></a></td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div>
</div>
<?= $this->endSection() ?>
