<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-settings">
<?= $this->include('partials/module_subnav') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc(lang('Settings.title')) ?></h2>
        <p class="page-subheading"><?= esc(lang('Settings.subtitle')) ?></p>
    </div>
    <?php if (! empty($canManageUsers)): ?>
        <a href="<?= site_url('module/settings/users') ?>" class="btn btn-secondary"><?= esc(lang('Settings.manage_users')) ?></a>
    <?php endif; ?>
</div>

<div class="card card-elevated form-card">
    <div class="card-body">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-error">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <div><?= esc($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('module/settings') ?>" class="app-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name"><?= esc(lang('Settings.business_name')) ?></label>
                <input type="text" id="name" name="name" value="<?= esc(old('name', $tenant['name'])) ?>" required maxlength="191">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="timezone"><?= esc(lang('Settings.timezone')) ?></label>
                    <select id="timezone" name="timezone" required>
                        <?php foreach (['Asia/Tehran', 'UTC', 'Europe/London', 'America/New_York'] as $tz): ?>
                            <option value="<?= $tz ?>" <?= old('timezone', $tenant['timezone']) === $tz ? 'selected' : '' ?>><?= esc($tz) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="currency"><?= esc(lang('Settings.currency')) ?></label>
                    <select id="currency" name="currency" required>
                        <?php foreach (['IRR', 'USD', 'EUR'] as $cur): ?>
                            <option value="<?= $cur ?>" <?= old('currency', $tenant['currency']) === $cur ? 'selected' : '' ?>><?= esc($cur) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="fiscal_year_start"><?= esc(lang('Settings.fiscal_year_start')) ?></label>
                <select id="fiscal_year_start" name="fiscal_year_start" required>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= (int) old('fiscal_year_start', $tenant['fiscal_year_start']) === $m ? 'selected' : '' ?>>
                            <?= esc(lang('Settings.month_' . $m)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="settings-meta">
                <div class="settings-meta-row">
                    <span class="text-muted"><?= esc(lang('Settings.plan')) ?></span>
                    <span class="badge"><?= esc($tenant['plan']) ?></span>
                </div>
                <div class="settings-meta-row">
                    <span class="text-muted"><?= esc(lang('App.status')) ?></span>
                    <span class="badge badge-<?= esc($tenant['status']) ?>"><?= esc(lang('App.' . $tenant['status'])) ?></span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
            </div>
        </form>
    </div>
</div>
</div>
<?= $this->endSection() ?>
