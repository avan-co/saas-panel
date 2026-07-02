<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-platform">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc($tenant ? lang('Platform.edit_tenant') : lang('Platform.create_tenant')) ?></h2></div>
<div class="card form-card"><div class="card-body">
<?php
$action = $tenant
    ? site_url('platform/tenants/' . $tenant['id'] . '/update')
    : site_url('platform/tenants/store');
?>
<form method="post" action="<?= $action ?>" class="app-form">
<?= csrf_field() ?>
<div class="form-group"><label><?= esc(lang('Platform.tenant_name')) ?></label><input type="text" name="name" value="<?= esc(old('name', $tenant['name'] ?? '')) ?>" required></div>
<?php if (! $tenant): ?>
<div class="form-group"><label><?= esc(lang('Platform.slug')) ?></label><input type="text" name="slug" value="<?= esc(old('slug')) ?>" required pattern="[a-z0-9\-]+" dir="ltr"></div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Platform.owner_name')) ?></label><input type="text" name="owner_name" value="<?= esc(old('owner_name')) ?>" required></div>
    <div class="form-group"><label><?= esc(lang('Platform.owner_email')) ?></label><input type="email" name="owner_email" value="<?= esc(old('owner_email')) ?>" required dir="ltr"></div>
</div>
<div class="form-group"><label><?= esc(lang('Platform.owner_password')) ?></label><input type="password" name="owner_password" placeholder="password" dir="ltr"><small><?= esc(lang('Platform.owner_password_hint')) ?></small></div>
<?php endif; ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('App.status')) ?></label>
        <select name="status"><?php foreach (['active','suspended','trial'] as $s): ?><option value="<?= $s ?>" <?= old('status', $tenant['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= esc(lang('App.' . $s)) ?></option><?php endforeach; ?></select>
    </div>
    <div class="form-group"><label><?= esc(lang('Platform.plan')) ?></label><input type="text" name="plan" value="<?= esc(old('plan', $tenant['plan'] ?? 'starter')) ?>" required></div>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Platform.subscription_start')) ?></label><input type="date" name="subscription_starts_at" value="<?= esc(old('subscription_starts_at', substr($tenant['subscription_starts_at'] ?? date('Y-m-d'), 0, 10))) ?>" dir="ltr"></div>
    <div class="form-group"><label><?= esc(lang('Platform.subscription_end')) ?></label><input type="date" name="subscription_ends_at" value="<?= esc(old('subscription_ends_at', substr($tenant['subscription_ends_at'] ?? date('Y-m-d', strtotime('+1 year')), 0, 10))) ?>" dir="ltr"></div>
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
