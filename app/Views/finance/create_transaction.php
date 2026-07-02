<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/breadcrumb') ?>

<?php
$moduleTabs = [
    ['key' => 'overview', 'label' => lang('Finance.overview'), 'url' => site_url('module/finance')],
    ['key' => 'transactions', 'label' => lang('Finance.transactions'), 'url' => site_url('module/finance/transactions')],
];
echo $this->include('partials/module_tabs');
?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc(lang('Finance.new_transaction')) ?></h2>
    </div>
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

        <form method="post" action="<?= site_url('module/finance/transactions/store') ?>" class="app-form">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="type"><?= esc(lang('Finance.amount')) ?> — <?= esc(lang('App.status')) ?></label>
                    <select id="type" name="type" required>
                        <option value="income"><?= esc(lang('Finance.type_income')) ?></option>
                        <option value="expense" selected><?= esc(lang('Finance.type_expense')) ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="txn_date"><?= esc(lang('Finance.date')) ?></label>
                    <input type="date" id="txn_date" name="txn_date" value="<?= esc(old('txn_date', date('Y-m-d'))) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="account_id"><?= esc(lang('Finance.account')) ?></label>
                    <select id="account_id" name="account_id" required>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= $account['id'] ?>" <?= old('account_id') == $account['id'] ? 'selected' : '' ?>>
                                <?= esc($account['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="category_id"><?= esc(lang('Finance.category')) ?></label>
                    <select id="category_id" name="category_id">
                        <option value="">—</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= old('category_id') == $cat['id'] ? 'selected' : '' ?>>
                                <?= esc($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="amount"><?= esc(lang('Finance.amount')) ?></label>
                <input type="number" id="amount" name="amount" min="1" step="1" value="<?= esc(old('amount')) ?>" required>
            </div>

            <div class="form-group">
                <label for="description"><?= esc(lang('Finance.description')) ?></label>
                <input type="text" id="description" name="description" value="<?= esc(old('description')) ?>" maxlength="255">
            </div>

            <div class="form-actions">
                <a href="<?= site_url('module/finance/transactions') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a>
                <button type="submit" class="btn btn-primary"><?= esc(lang('Finance.save_transaction')) ?></button>
            </div>
        </form>
    </div>
</div>
</div>
<?= $this->endSection() ?>
