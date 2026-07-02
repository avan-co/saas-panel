<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/project_subnav') ?>
<h2 class="page-heading"><?= esc(lang('Projects.risks')) ?></h2>
<div class="content-grid">
<div class="card"><div class="card-header"><h3><?= esc(lang('Projects.add_risk')) ?></h3></div>
<?php if ($canEdit): ?><div class="card-body">
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/risks/store') ?>" class="app-form"><?= csrf_field() ?>
<input type="text" name="title" required placeholder="<?= esc(lang('Projects.add_risk')) ?>">
<textarea name="mitigation" placeholder="<?= esc(lang('Projects.mitigation')) ?>"></textarea>
<button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
</form></div><?php endif; ?>
<div class="table-wrap"><table class="data-table"><tbody>
<?php foreach ($risks as $r): ?>
<tr><td><?= esc($r['title']) ?></td><td><?= esc($r['probability']) ?>/<?= esc($r['impact']) ?></td><td><?= esc($r['owner_name'] ?? '—') ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>

<div class="card"><div class="card-header"><h3><?= esc(lang('Projects.issues')) ?></h3></div>
<?php if ($canEdit): ?><div class="card-body">
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/issues/store') ?>" class="app-form"><?= csrf_field() ?>
<select name="type"><?php foreach (['technical','financial','supply','client','internal'] as $t): ?>
<option value="<?= $t ?>"><?= esc(lang('Projects.issue_type_'.$t)) ?></option><?php endforeach; ?>
</select>
<input type="text" name="title" required>
<button type="submit" class="btn btn-primary"><?= esc(lang('Projects.add_issue')) ?></button>
</form></div><?php endif; ?>
<div class="table-wrap"><table class="data-table"><tbody>
<?php foreach ($issues as $i): ?>
<tr><td><span class="badge"><?= esc($i['type']) ?></span></td><td><?= esc($i['title']) ?></td><td><?= esc($i['status']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
</div></div>
<?= $this->endSection() ?>
