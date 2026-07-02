<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/project_subnav') ?>
<h2 class="page-heading"><?= esc(lang('Projects.wiki')) ?></h2>
<div class="content-grid">
<div class="card"><div class="card-header"><h3><?= esc(lang('Projects.add_wiki_page')) ?></h3></div>
<?php if ($canEdit): ?><div class="card-body">
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/wiki/pages') ?>" class="app-form"><?= csrf_field() ?>
<input type="text" name="title" required>
<textarea name="content" rows="6"></textarea>
<button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
</form></div><?php endif; ?>
<?php foreach ($pages as $page): ?>
<div class="card-body" style="border-top:1px solid var(--border)"><h4><?= esc($page['title']) ?></h4><div><?= nl2br(esc($page['content'] ?? '')) ?></div></div>
<?php endforeach; ?>
</div>

<div class="card"><div class="card-header"><h3><?= esc(lang('Projects.decisions')) ?></h3></div>
<?php if ($canEdit): ?><div class="card-body">
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/wiki/decisions') ?>" class="app-form"><?= csrf_field() ?>
<textarea name="decision" required placeholder="<?= esc(lang('Projects.add_decision')) ?>"></textarea>
<label><input type="checkbox" name="create_task" value="1"> <?= esc(lang('Projects.create_task_from_decision')) ?></label>
<button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
</form></div><?php endif; ?>
<?php foreach ($decisions as $d): ?>
<div class="card-body" style="border-top:1px solid var(--border)">
<p><?= esc($d['decision']) ?></p>
<span class="text-muted"><?= esc($d['owner_name'] ?? '') ?> <?= !empty($d['due_date'])?'— '.jalali_date($d['due_date']):'' ?></span>
<?php if ($d['task_id']): ?><a href="<?= site_url('module/projects/' . $project['id'] . '/tasks/' . $d['task_id']) ?>" class="btn btn-ghost btn-sm">Task</a><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div></div>
<?= $this->endSection() ?>
