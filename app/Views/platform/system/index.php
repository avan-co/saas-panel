<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-platform">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header">
    <h2 class="page-heading"><?= esc(lang('Platform.system_settings')) ?></h2>
    <p class="page-subheading"><?= esc(lang('Platform.system_settings_subtitle')) ?></p>
</div>

<div class="card card-elevated"><div class="card-header"><h3><?= esc(lang('Platform.infrastructure')) ?></h3></div>
<div class="card-body">
    <div class="settings-meta-row"><span class="text-muted"><?= esc(lang('Platform.app_url')) ?></span><span dir="ltr"><?= esc($appUrl) ?></span></div>
    <div class="settings-meta-row"><span class="text-muted"><?= esc(lang('Settings.modian')) ?></span>
        <span><?= $modianReady ? esc(lang('Settings.modian_ready')) : esc(lang('Platform.modian_configure_env')) ?></span>
    </div>
    <?php if (! $modianReady): ?>
    <p class="text-muted" style="margin-top:12px"><?= esc(lang('Platform.modian_env_hint')) ?></p>
    <?php endif; ?>
</div></div>

<div class="alert alert-warning" style="margin-top:20px"><?= esc(lang('Platform.tenant_settings_note')) ?></div>
</div>
<?= $this->endSection() ?>
