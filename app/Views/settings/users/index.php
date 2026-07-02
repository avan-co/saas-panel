<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-settings">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Settings.users')) ?></h2></div>

<?= view('partials/org_chart', ['orgTree' => $orgTree ?? []]) ?>

<div class="content-grid">
    <div class="card form-card">
        <div class="card-header"><h3><?= esc(lang('Settings.add_user')) ?></h3></div>
        <div class="card-body">
            <form method="post" action="<?= site_url('module/settings/users/store') ?>" class="app-form">
                <?= csrf_field() ?>
                <div class="form-group"><label><?= esc(lang('Settings.user_name')) ?></label><input type="text" name="name" required></div>
                <div class="form-group"><label><?= esc(lang('Settings.user_email')) ?></label><input type="email" name="email" required dir="ltr"></div>
                <div class="form-group"><label><?= esc(lang('Settings.user_password')) ?></label><input type="password" name="password" minlength="8" required></div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?= esc(lang('Settings.user_role')) ?></label>
                        <select name="role">
                            <?php foreach (['admin','accountant','manager','hr','employee','viewer'] as $role): ?>
                                <option value="<?= $role ?>"><?= esc(lang('Settings.role_' . $role)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label><?= esc(lang('Settings.department')) ?></label><input type="text" name="department"></div>
                </div>
                <div class="form-group"><label><?= esc(lang('Settings.manager')) ?></label>
                    <select name="manager_id"><option value="">—</option>
                        <?php foreach ($members as $m): ?><option value="<?= $m['id'] ?>"><?= esc($m['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <fieldset class="permissions-fieldset">
                    <legend><?= esc(lang('Settings.custom_permissions')) ?></legend>
                    <?php foreach (['finance.view','finance.transactions','finance.invoices','finance.contacts','projects.manage','projects.view','projects.tasks'] as $perm): ?>
                        <label class="checkbox-label"><input type="checkbox" name="permissions[]" value="<?= $perm ?>"> <?= esc($perm) ?></label>
                    <?php endforeach; ?>
                </fieldset>
                <button type="submit" class="btn btn-primary"><?= esc(lang('Settings.add_user')) ?></button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3><?= esc(lang('Settings.team')) ?></h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th><?= esc(lang('Settings.user_name')) ?></th><th><?= esc(lang('Settings.user_email')) ?></th><th><?= esc(lang('Settings.user_role')) ?></th><th><?= esc(lang('Settings.department')) ?></th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <td><?= esc($m['name']) ?></td>
                            <td dir="ltr"><?= esc($m['email']) ?></td>
                            <td><span class="badge"><?= esc(lang('Settings.role_' . $m['role'])) ?></span></td>
                            <td><?= esc($m['department'] ?? '—') ?></td>
                            <td>
                                <?php if (! in_array($m['role'], ['owner'], true)): ?>
                                <details class="user-edit-details">
                                    <summary class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></summary>
                                    <form method="post" action="<?= site_url('module/settings/users/' . $m['id'] . '/update') ?>" class="app-form" style="padding:12px">
                                        <?= csrf_field() ?>
                                        <select name="role">
                                            <?php foreach (['admin','accountant','manager','hr','employee','viewer'] as $role): ?>
                                                <option value="<?= $role ?>" <?= $m['role'] === $role ? 'selected' : '' ?>><?= esc(lang('Settings.role_' . $role)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" name="department" value="<?= esc($m['department'] ?? '') ?>" placeholder="<?= esc(lang('Settings.department')) ?>">
                                        <select name="manager_id"><option value="">—</option>
                                            <?php foreach ($members as $mgr): if ((int)$mgr['id'] === (int)$m['id']) continue; ?>
                                                <option value="<?= $mgr['id'] ?>" <?= (int)($m['manager_id'] ?? 0) === (int)$mgr['id'] ? 'selected' : '' ?>><?= esc($mgr['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm"><?= esc(lang('App.save')) ?></button>
                                    </form>
                                </details>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
