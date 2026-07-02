<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-platform">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Platform.edit_tenant')) ?>: <?= esc($tenant['name']) ?></h2></div>
<div class="card form-card"><div class="card-body">
<form method="post" action="<?= site_url('platform/tenants/' . $tenant['id'] . '/update') ?>" class="app-form">
<?= csrf_field() ?>
<div class="form-group"><label><?= esc(lang('Platform.tenant_name')) ?></label><input type="text" name="name" value="<?= esc(old('name', $tenant['name'])) ?>" required></div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('App.status')) ?></label>
        <select name="status"><?php foreach (['active','suspended','trial'] as $s): ?><option value="<?= $s ?>" <?= old('status', $tenant['status']) === $s ? 'selected' : '' ?>><?= esc(lang('App.' . $s)) ?></option><?php endforeach; ?></select>
    </div>
    <div class="form-group"><label><?= esc(lang('Platform.plan')) ?></label><input type="text" name="plan" value="<?= esc(old('plan', $tenant['plan'])) ?>" required></div>
</div>
<div class="form-group"><label><?= esc(lang('Platform.modules')) ?></label>
    <div class="checkbox-grid">
        <?php foreach ($allModules as $mod): ?>
            <label class="checkbox-label"><input type="checkbox" name="modules[]" value="<?= $mod['id'] ?>" <?= in_array($mod['id'], $enabledIds, true) ? 'checked' : '' ?>> <?= esc(lang('App.modules.' . $mod['code'])) ?></label>
        <?php endforeach; ?>
    </div>
</div>
<div class="form-actions"><a href="<?= site_url('platform/tenants') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a><button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button></div>
</form></div></div>
</div>
<?= $this->endSection() ?>
