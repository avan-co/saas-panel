<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/project_subnav') ?>
<h2 class="page-heading"><?= esc(lang('Projects.files')) ?></h2>
<?php if ($canEdit): ?>
<div class="card form-card"><div class="card-body">
<form method="post" enctype="multipart/form-data" action="<?= site_url('module/projects/' . $project['id'] . '/files/store') ?>" class="app-form"><?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Projects.upload_file')) ?></label><input type="file" name="file" required></div>
    <div class="form-group"><label><?= esc(lang('Projects.task_title')) ?></label><input type="text" name="title"></div>
</div>
<button type="submit" class="btn btn-primary"><?= esc(lang('Projects.upload_file')) ?></button>
</form></div></div>
<?php endif; ?>
<div class="card" style="margin-top:16px"><div class="table-wrap"><table class="data-table">
<thead><tr><th><?= esc(lang('Projects.task_title')) ?></th><th><?= esc(lang('Projects.version')) ?></th><th><?= esc(lang('App.status')) ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($documents as $doc): ?>
<tr>
    <td><?= esc($doc['title']) ?> <span class="text-muted text-sm"><?= esc($doc['original_name'] ?? '') ?></span></td>
    <td>v<?= (int) $doc['version'] ?></td>
    <td><span class="badge"><?= esc(lang('Projects.approval_' . ($doc['approval_status'] ?? 'draft'))) ?></span></td>
    <td>
        <a href="<?= site_url('module/projects/' . $project['id'] . '/files/' . $doc['id'] . '/download') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.download')) ?></a>
        <?php if ($canEdit): ?>
        <form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/files/' . $doc['id'] . '/approve') ?>" class="inline-form"><?= csrf_field() ?>
        <input type="hidden" name="approval_status" value="approved"><button type="submit" class="btn btn-ghost btn-sm">✓</button>
        </form>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div>
</div>
<?= $this->endSection() ?>
