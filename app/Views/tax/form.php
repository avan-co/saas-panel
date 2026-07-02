<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-tax">
<?= $this->include('partials/breadcrumb') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc($period ? lang('Tax.edit_period') : lang('Tax.new_period')) ?></h2>
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
        $action = $period
            ? site_url('module/tax/' . $period['id'] . '/update')
            : site_url('module/tax/store');
        ?>

        <form method="post" action="<?= $action ?>" class="app-form">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="period_year"><?= esc(lang('Tax.year')) ?></label>
                    <input type="number" id="period_year" name="period_year" min="2000" max="2100" value="<?= esc(old('period_year', $period['period_year'] ?? date('Y'))) ?>" required>
                </div>
                <div class="form-group">
                    <label for="period_quarter"><?= esc(lang('Tax.quarter')) ?></label>
                    <select id="period_quarter" name="period_quarter" required>
                        <?php for ($q = 1; $q <= 4; $q++): ?>
                            <option value="<?= $q ?>" <?= (int) old('period_quarter', $period['period_quarter'] ?? 1) === $q ? 'selected' : '' ?>>Q<?= $q ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="taxable_income"><?= esc(lang('Tax.taxable_income')) ?></label>
                    <input type="number" id="taxable_income" name="taxable_income" min="0" step="1" value="<?= esc(old('taxable_income', $period['taxable_income'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="tax_amount"><?= esc(lang('Tax.tax_amount')) ?></label>
                    <input type="number" id="tax_amount" name="tax_amount" min="0" step="1" value="<?= esc(old('tax_amount', $period['tax_amount'] ?? '')) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="due_date"><?= esc(lang('Tax.due_date')) ?></label>
                    <input type="date" id="due_date" name="due_date" value="<?= esc(old('due_date', $period['due_date'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label for="status"><?= esc(lang('App.status')) ?></label>
                    <select id="status" name="status" required>
                        <?php foreach (['pending', 'filed', 'paid'] as $st): ?>
                            <option value="<?= $st ?>" <?= old('status', $period['status'] ?? 'pending') === $st ? 'selected' : '' ?>>
                                <?= esc(lang('Tax.status_' . $st)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= site_url('module/tax') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a>
                <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
            </div>
        </form>
    </div>
</div>
</div>
<?= $this->endSection() ?>
