<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-settings">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header">
    <h2 class="page-heading"><?= esc(lang('Settings.module_harmony')) ?></h2>
    <p class="page-subheading"><?= esc(lang('Settings.module_harmony_subtitle')) ?></p>
</div>

<div class="card card-elevated"><div class="card-header"><h3><?= esc(lang('Settings.enabled_modules')) ?></h3></div>
<div class="card-body">
<?php if ($modules === []): ?>
    <p class="text-muted"><?= esc(lang('Settings.no_modules')) ?></p>
<?php else: ?>
    <div class="kpi-grid kpi-grid-4">
    <?php foreach ($modules as $module): ?>
        <div class="kpi-card"><span class="kpi-label"><?= esc(lang('App.modules.' . $module['code'])) ?></span><span class="badge"><?= esc($module['code']) ?></span></div>
    <?php endforeach; ?>
    </div>
    <?php if (! $canManage): ?>
    <p class="text-muted" style="margin-top:12px"><?= esc(lang('Settings.modules_managed_by_platform')) ?></p>
    <?php endif; ?>
<?php endif; ?>
</div></div>

<?php if ($links !== []): ?>
<div class="card card-elevated" style="margin-top:20px"><div class="card-header"><h3><?= esc(lang('Settings.module_links')) ?></h3></div>
<div class="table-wrap"><table class="data-table">
<thead><tr><th><?= esc(lang('Settings.link_from')) ?></th><th><?= esc(lang('Settings.link_to')) ?></th><th><?= esc(lang('Finance.description')) ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($links as $link): ?>
<tr>
    <td><?= esc($link['from']) ?></td>
    <td><?= esc($link['to']) ?></td>
    <td><?= esc($link['description']) ?></td>
    <td><a href="<?= site_url($link['route']) ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.view')) ?></a></td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php else: ?>
<div class="alert alert-warning" style="margin-top:20px"><?= esc(lang('Settings.module_links_hint')) ?></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
