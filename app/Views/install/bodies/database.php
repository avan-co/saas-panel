<p class="install-help"><?= esc(lang('Install.database_help')) ?></p>

<form method="post" action="<?= site_url('install/database') ?>" class="install-form">
    <?= csrf_field() ?>

    <div class="form-row">
        <div class="form-group">
            <label for="hostname"><?= esc(lang('Install.hostname')) ?></label>
            <input type="text" id="hostname" name="hostname" value="<?= esc(old('hostname', $db['hostname'])) ?>" required>
        </div>
        <div class="form-group">
            <label for="port"><?= esc(lang('Install.port')) ?></label>
            <input type="number" id="port" name="port" value="<?= esc(old('port', $db['port'])) ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label for="database"><?= esc(lang('Install.database')) ?></label>
        <input type="text" id="database" name="database" value="<?= esc(old('database', $db['database'])) ?>" required>
    </div>

    <div class="form-group">
        <label for="username"><?= esc(lang('Install.username')) ?></label>
        <input type="text" id="username" name="username" value="<?= esc(old('username', $db['username'])) ?>" required>
    </div>

    <div class="form-group">
        <label for="password"><?= esc(lang('Install.password')) ?></label>
        <input type="password" id="password" name="password" value="<?= esc(old('password', $db['password'])) ?>">
    </div>

    <div class="install-actions">
        <a href="<?= site_url('install') ?>" class="btn btn-secondary"><?= esc(lang('Install.back')) ?></a>
        <button type="submit" class="btn btn-primary"><?= esc(lang('Install.next')) ?></button>
    </div>
</form>
