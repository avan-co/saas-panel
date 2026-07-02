<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>

<div class="page-header">
    <h2 class="page-heading"><?= esc($account ? lang('Finance.edit_account') : lang('Finance.new_account')) ?></h2>
</div>

<div class="card card-elevated form-card">
    <div class="card-body">
        <?php $action = $account ? site_url('module/finance/accounts/' . $account['id'] . '/update') : site_url('module/finance/accounts/store'); ?>
        <form method="post" action="<?= $action ?>" class="app-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name"><?= esc(lang('Finance.account')) ?></label>
                <input type="text" id="name" name="name" value="<?= esc(old('name', $account['name'] ?? '')) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="type"><?= esc(lang('Finance.account_type')) ?></label>
                    <select id="type" name="type" required>
                        <?php foreach (['bank','cash','card','wallet','petty_cash','personal'] as $t): ?>
                            <option value="<?= $t ?>" <?= old('type', $account['type'] ?? 'bank') === $t ? 'selected' : '' ?>><?= esc(lang('Finance.account_type_' . $t)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="balance"><?= esc(lang('Finance.opening_balance')) ?></label>
                    <input type="number" id="balance" name="balance" step="1" value="<?= esc(old('balance', $account['balance'] ?? '0')) ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="currency"><?= esc(lang('Settings.currency')) ?></label>
                <input type="text" id="currency" name="currency" value="<?= esc(old('currency', $account['currency'] ?? 'IRR')) ?>" maxlength="8">
            </div>
            <div class="form-actions">
                <a href="<?= site_url('module/finance/accounts') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a>
                <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
            </div>
        </form>
    </div>
</div>
</div>
<?= $this->endSection() ?>
