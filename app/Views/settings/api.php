<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-settings">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header">
    <h2 class="page-heading"><?= esc(lang('Settings.api_access')) ?></h2>
    <p class="page-subheading"><?= esc(lang('Settings.api_access_subtitle')) ?></p>
</div>
<?php if ($newKey = session()->getFlashdata('new_api_key')): ?>
<div class="alert alert-warning"><?= esc(lang('Settings.api_key_copy')) ?>: <code dir="ltr"><?= esc($newKey) ?></code></div>
<?php endif; ?>
<div class="card card-elevated"><div class="card-header"><h3><?= esc(lang('Settings.api_keys')) ?></h3></div><div class="card-body">
<form method="post" action="<?= site_url('module/settings/api/keys') ?>" class="app-form"><?= csrf_field() ?>
<div class="form-row"><div class="form-group"><label><?= esc(lang('Settings.api_key_name')) ?></label><input type="text" name="name" required></div><button type="submit" class="btn btn-primary"><?= esc(lang('Settings.create_api_key')) ?></button></div>
</form>
<table class="data-table"><thead><tr><th><?= esc(lang('Settings.api_key_name')) ?></th><th><?= esc(lang('Settings.prefix')) ?></th><th></th></tr></thead>
<tbody><?php foreach ($apiKeys as $key): ?><tr><td><?= esc($key['name']) ?></td><td dir="ltr"><?= esc($key['key_prefix']) ?>…</td>
<td><form method="post" action="<?= site_url('module/settings/api/keys/' . $key['id'] . '/delete') ?>"><?= csrf_field() ?><button type="submit" class="btn btn-ghost btn-sm"><?= esc(lang('App.delete')) ?></button></form></td></tr><?php endforeach; ?></tbody></table>
</div></div>
<div class="card card-elevated" style="margin-top:20px"><div class="card-header"><h3><?= esc(lang('Settings.webhooks')) ?></h3></div><div class="card-body">
<form method="post" action="<?= site_url('module/settings/api/webhooks') ?>" class="app-form"><?= csrf_field() ?>
<div class="form-group"><label>URL</label><input type="url" name="url" required dir="ltr"></div>
<div class="form-group"><label><?= esc(lang('Settings.webhook_events')) ?></label><input type="text" name="events" placeholder="transaction.created, *"></div>
<button type="submit" class="btn btn-primary"><?= esc(lang('Settings.add_webhook')) ?></button>
</form>
</div></div>
</div>
<?= $this->endSection() ?>
