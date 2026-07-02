<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<form method="post" action="<?= site_url('login') ?>" class="auth-form">
    <?= csrf_field() ?>

    <h2><?= esc(lang('Auth.login_title')) ?></h2>
    <p class="auth-subtitle"><?= esc(lang('Auth.login_subtitle')) ?></p>

    <div class="form-group">
        <label for="email"><?= esc(lang('Auth.email')) ?></label>
        <input type="email" id="email" name="email" value="<?= esc(old('email', 'admin@demo.local')) ?>" required autofocus>
    </div>

    <div class="form-group">
        <label for="password"><?= esc(lang('Auth.password')) ?></label>
        <input type="password" id="password" name="password" value="password" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block"><?= esc(lang('Auth.login')) ?></button>

    <p class="demo-hint"><?= esc(lang('Auth.demo_hint')) ?></p>
</form>
<?= $this->endSection() ?>
