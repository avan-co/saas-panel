<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-settings">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Settings.users')) ?></h2></div>

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
                            <option value="admin"><?= esc(lang('Settings.role_admin')) ?></option>
                            <option value="accountant"><?= esc(lang('Settings.role_accountant')) ?></option>
                            <option value="hr"><?= esc(lang('Settings.role_hr')) ?></option>
                            <option value="viewer"><?= esc(lang('Settings.role_viewer')) ?></option>
                        </select>
                    </div>
                    <div class="form-group"><label><?= esc(lang('Settings.department')) ?></label><input type="text" name="department"></div>
                </div>
                <button type="submit" class="btn btn-primary"><?= esc(lang('Settings.add_user')) ?></button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3><?= esc(lang('Settings.team')) ?></h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th><?= esc(lang('Settings.user_name')) ?></th><th><?= esc(lang('Settings.user_email')) ?></th><th><?= esc(lang('Settings.user_role')) ?></th><th><?= esc(lang('Settings.department')) ?></th></tr></thead>
                <tbody>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <td><?= esc($m['name']) ?></td>
                            <td dir="ltr"><?= esc($m['email']) ?></td>
                            <td><span class="badge"><?= esc(lang('Settings.role_' . $m['role'])) ?></span></td>
                            <td><?= esc($m['department'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
