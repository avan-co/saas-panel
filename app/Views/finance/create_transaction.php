<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc($transaction ? lang('Finance.edit_transaction') : lang('Finance.new_transaction')) ?></h2>
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

        <?php
        $action = $transaction
            ? site_url('module/finance/transactions/' . $transaction['id'] . '/update')
            : site_url('module/finance/transactions/store');
        $currentType = old('type', $transaction['type'] ?? 'expense');
        $txnDateValue = old('txn_date');
        if ($txnDateValue === null) {
            $txnDateValue = $transaction
                ? jalali_date($transaction['txn_date'])
                : today_for_input($locale ?? 'fa');
        }
        ?>

        <form method="post" action="<?= $action ?>" class="app-form" id="txnForm">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="type"><?= esc(lang('Finance.type')) ?></label>
                    <select id="type" name="type" required>
                        <option value="income" <?= $currentType === 'income' ? 'selected' : '' ?>><?= esc(lang('Finance.type_income')) ?></option>
                        <option value="expense" <?= $currentType === 'expense' ? 'selected' : '' ?>><?= esc(lang('Finance.type_expense')) ?></option>
                        <option value="transfer" <?= $currentType === 'transfer' ? 'selected' : '' ?>><?= esc(lang('Finance.type_transfer')) ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="txn_date"><?= esc(lang('Finance.date')) ?></label>
                    <input type="text" id="txn_date" name="txn_date" class="jalali-date" value="<?= esc($txnDateValue) ?>" required autocomplete="off">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="account_id"><?= esc(lang('Finance.account')) ?></label>
                    <select id="account_id" name="account_id" required>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= $account['id'] ?>" <?= (string) old('account_id', $transaction['account_id'] ?? '') === (string) $account['id'] ? 'selected' : '' ?>>
                                <?= esc($account['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" id="transferToGroup" style="<?= $currentType === 'transfer' ? '' : 'display:none' ?>">
                    <label for="transfer_to_account_id"><?= esc(lang('Finance.transfer_to')) ?></label>
                    <select id="transfer_to_account_id" name="transfer_to_account_id">
                        <option value="">—</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= $account['id'] ?>" <?= (string) old('transfer_to_account_id', $transaction['transfer_to_account_id'] ?? '') === (string) $account['id'] ? 'selected' : '' ?>>
                                <?= esc($account['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row" id="categoryProjectRow">
                <div class="form-group" id="categoryGroup">
                    <label for="category_id"><?= esc(lang('Finance.category')) ?></label>
                    <select id="category_id" name="category_id">
                        <option value="">—</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" data-type="<?= esc($cat['type']) ?>" <?= (string) old('category_id', $transaction['category_id'] ?? '') === (string) $cat['id'] ? 'selected' : '' ?>>
                                <?= esc($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (! empty($projects)): ?>
                <div class="form-group">
                    <label for="project_id"><?= esc(lang('Finance.project')) ?></label>
                    <select id="project_id" name="project_id">
                        <option value="">—</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['id'] ?>" <?= (string) old('project_id', $transaction['project_id'] ?? '') === (string) $project['id'] ? 'selected' : '' ?>>
                                <?= esc($project['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="amount"><?= esc(lang('Finance.amount')) ?></label>
                    <input type="number" id="amount" name="amount" min="1" step="1" value="<?= esc(old('amount', $transaction['amount'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="contact_name"><?= esc(lang('Finance.contact')) ?></label>
                    <input type="text" id="contact_name" name="contact_name" value="<?= esc(old('contact_name', $transaction['contact_name'] ?? '')) ?>" maxlength="120">
                </div>
            </div>

            <div class="form-group">
                <label for="description"><?= esc(lang('Finance.description')) ?></label>
                <input type="text" id="description" name="description" value="<?= esc(old('description', $transaction['description'] ?? '')) ?>" maxlength="255">
            </div>

            <div class="form-actions">
                <a href="<?= site_url('module/finance/transactions') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a>
                <button type="submit" class="btn btn-primary"><?= esc($transaction ? lang('App.save') : lang('Finance.save_transaction')) ?></button>
            </div>
        </form>
    </div>
</div>
</div>
<?= $this->endSection() ?>
