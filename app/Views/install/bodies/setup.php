<p class="install-help"><?= esc(lang('Install.setup_help')) ?></p>

<form method="post" action="<?= site_url('install/setup') ?>" class="install-form">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="baseURL"><?= esc(lang('Install.base_url')) ?></label>
        <input type="url" id="baseURL" name="baseURL" value="<?= esc(old('baseURL', $baseURL)) ?>" required dir="ltr">
    </div>

    <div class="form-group">
        <label for="admin_name"><?= esc(lang('Install.admin_name')) ?></label>
        <input type="text" id="admin_name" name="admin_name" value="<?= esc(old('admin_name')) ?>" required>
    </div>

    <div class="form-group">
        <label for="admin_email"><?= esc(lang('Install.admin_email')) ?></label>
        <input type="email" id="admin_email" name="admin_email" value="<?= esc(old('admin_email')) ?>" required dir="ltr">
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="admin_password"><?= esc(lang('Install.admin_password')) ?></label>
            <input type="password" id="admin_password" name="admin_password" required minlength="8">
        </div>
        <div class="form-group">
            <label for="admin_password_confirm"><?= esc(lang('Install.admin_password_confirm')) ?></label>
            <input type="password" id="admin_password_confirm" name="admin_password_confirm" required minlength="8">
        </div>
    </div>

    <div class="form-group form-check">
        <label>
            <input type="checkbox" name="seed_demo" value="1" checked>
            <?= esc(lang('Install.seed_demo')) ?>
        </label>
    </div>

    <div class="install-actions">
        <a href="<?= site_url('install/database') ?>" class="btn btn-secondary"><?= esc(lang('Install.back')) ?></a>
        <button type="submit" class="btn btn-primary"><?= esc(lang('Install.install_now')) ?></button>
    </div>
</form>
