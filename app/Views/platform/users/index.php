<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="platform-page">
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Platform.users')) ?></h2></div>
<div class="card"><div class="table-wrap">
<table class="data-table">
<thead><tr><th><?= esc(lang('Settings.user_name')) ?></th><th><?= esc(lang('Settings.user_email')) ?></th><th><?= esc(lang('Platform.tenants')) ?></th><th><?= esc(lang('Platform.platform_admin')) ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($users as $user): ?>
<tr>
    <td><?= esc($user['name']) ?></td>
    <td dir="ltr"><?= esc($user['email']) ?></td>
    <td><?php foreach ($user['tenants'] as $t): ?><span class="module-tag"><?= esc($t['name']) ?> (<?= esc($t['role']) ?>)</span> <?php endforeach; ?></td>
    <td><?= $user['is_platform_admin'] ? '✓' : '—' ?></td>
    <td>
        <form method="post" action="<?= site_url('platform/users/' . $user['id'] . '/toggle-admin') ?>" class="inline-form"><?= csrf_field() ?>
            <button type="submit" class="btn btn-ghost btn-sm"><?= esc(lang('Platform.toggle_admin')) ?></button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
</div>
<?= $this->endSection() ?>
