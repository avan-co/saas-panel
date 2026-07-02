<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/project_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc($task['title']) ?></h2></div>
<div class="content-grid">
<div class="card"><div class="card-body">
<?php if ($canEdit): ?>
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/tasks/' . $task['id'] . '/update') ?>" class="app-form"><?= csrf_field() ?>
<div class="form-group"><label><?= esc(lang('Projects.task_title')) ?></label><input type="text" name="title" value="<?= esc($task['title']) ?>" required></div>
<div class="form-group"><label><?= esc(lang('Finance.description')) ?></label><textarea name="description" rows="3"><?= esc($task['description'] ?? '') ?></textarea></div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Projects.assignee')) ?></label>
        <select name="assignee_user_id"><option value="">—</option>
        <?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>" <?= (int)($task['assignee_user_id']??0)===(int)$u['user_id']?'selected':'' ?>><?= esc($u['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label><?= esc(lang('Projects.depends_on')) ?></label>
        <select name="depends_on_task_id"><option value="">—</option>
        <?php foreach ($allTasks as $t): if ((int)$t['id']===(int)$task['id']) continue; ?>
        <option value="<?= $t['id'] ?>" <?= (int)($task['depends_on_task_id']??0)===(int)$t['id']?'selected':'' ?>><?= esc($t['title']) ?></option>
        <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Projects.due_date')) ?></label><input type="text" name="due_date" class="jalali-date" value="<?= !empty($task['due_date'])?esc(jalali_date($task['due_date'])):'' ?>"></div>
    <div class="form-group"><label><?= esc(lang('Projects.estimated_hours')) ?></label><input type="number" name="estimated_hours" step="0.5" value="<?= esc($task['estimated_hours'] ?? '') ?>"></div>
    <div class="form-group"><label><?= esc(lang('App.status')) ?></label>
        <select name="status"><?php foreach (['backlog','todo','doing','review','testing','done'] as $st): ?>
        <option value="<?= $st ?>" <?= $task['status']===$st?'selected':'' ?>><?= esc(lang('Projects.status_'.$st)) ?></option><?php endforeach; ?>
        </select>
    </div>
</div>
<button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
</form>
<?php else: ?>
<p><?= esc($task['description'] ?? '') ?></p>
<?php if ($depends): ?><p class="text-muted"><?= esc(lang('Projects.depends_on')) ?>: <?= esc($depends['title']) ?></p><?php endif; ?>
<?php endif; ?>
</div></div>

<div class="card"><div class="card-header"><h3><?= esc(lang('Projects.checklist')) ?></h3></div><div class="card-body">
<?php foreach ($checklist as $item): ?>
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/tasks/' . $task['id'] . '/checklist/' . $item['id'] . '/toggle') ?>" class="inline-form"><?= csrf_field() ?>
<label><input type="checkbox" <?= $item['is_done']?'checked':'' ?> onchange="this.form.submit()"> <?= esc($item['title']) ?></label>
</form>
<?php endforeach; ?>
<?php if ($canEdit): ?>
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/tasks/' . $task['id'] . '/checklist') ?>" class="app-form" style="margin-top:12px"><?= csrf_field() ?>
<input type="text" name="title" placeholder="<?= esc(lang('Projects.checklist')) ?>"> <button type="submit" class="btn btn-sm btn-primary">+</button>
</form>
<?php endif; ?>
</div></div>
</div>

<div class="card" style="margin-top:20px"><div class="card-header"><h3><?= esc(lang('Projects.comments')) ?></h3></div><div class="card-body">
<?php foreach ($comments as $c): ?>
<div style="padding:8px 0;border-bottom:1px solid var(--border)"><strong><?= esc($c['user_name']) ?></strong> <span class="text-muted text-sm"><?= esc($c['created_at']) ?></span><p><?= esc($c['body']) ?></p></div>
<?php endforeach; ?>
<?php if ($canEdit): ?>
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/tasks/' . $task['id'] . '/comments') ?>" class="app-form" style="margin-top:12px"><?= csrf_field() ?>
<textarea name="body" rows="2" required></textarea>
<button type="submit" class="btn btn-primary"><?= esc(lang('Projects.add_comment')) ?></button>
</form>
<?php endif; ?>
</div></div>
</div>
<?= $this->endSection() ?>
