<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-settings">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Settings.audit_log')) ?></h2></div>
<div class="table-wrap"><table class="data-table">
<thead><tr><th><?= esc(lang('Settings.audit_time')) ?></th><th><?= esc(lang('Settings.audit_action')) ?></th><th><?= esc(lang('Settings.audit_entity')) ?></th><th><?= esc(lang('Finance.description')) ?></th></tr></thead>
<tbody>
<?php foreach ($logs as $log): ?>
<tr>
    <td><?= esc($log['created_at']) ?></td>
    <td><?= esc($log['action']) ?></td>
    <td><?= esc($log['entity_type']) ?> #<?= esc($log['entity_id'] ?? '') ?></td>
    <td><?= esc($log['summary'] ?? '') ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
</div>
<?= $this->endSection() ?>
