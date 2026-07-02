<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-settings">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Settings.period_locks')) ?></h2></div>
<div class="card form-card"><div class="card-body">
<form method="post" action="<?= site_url('module/settings/period-locks') ?>" class="app-form"><?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Settings.lock_year')) ?></label><input type="number" name="year" value="<?= date('Y') ?>" required></div>
    <div class="form-group"><label><?= esc(lang('Settings.lock_month')) ?></label><input type="number" name="month" min="1" max="12" value="<?= date('n') ?>" required></div>
</div>
<button type="submit" class="btn btn-primary"><?= esc(lang('Settings.lock_period')) ?></button>
</form></div></div>
<div class="table-wrap" style="margin-top:20px"><table class="data-table">
<thead><tr><th><?= esc(lang('Settings.lock_year')) ?></th><th><?= esc(lang('Settings.lock_month')) ?></th><th><?= esc(lang('Settings.locked_at')) ?></th></tr></thead>
<tbody><?php foreach ($locks as $lock): ?><tr><td><?= esc($lock['year']) ?></td><td><?= esc($lock['month']) ?></td><td><?= esc($lock['locked_at']) ?></td></tr><?php endforeach; ?></tbody>
</table></div>
</div>
<?= $this->endSection() ?>
