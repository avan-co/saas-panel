<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc($category ? lang('Finance.edit_category') : lang('Finance.new_category')) ?></h2></div>
<div class="card card-elevated form-card">
    <div class="card-body">
        <?php $action = $category ? site_url('module/finance/categories/' . $category['id'] . '/update') : site_url('module/finance/categories/store'); ?>
        <form method="post" action="<?= $action ?>" class="app-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name"><?= esc(lang('Finance.category')) ?></label>
                <input type="text" id="name" name="name" value="<?= esc(old('name', $category['name'] ?? '')) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="type"><?= esc(lang('Finance.category_type')) ?></label>
                    <select id="type" name="type">
                        <option value="expense" <?= old('type', $category['type'] ?? 'expense') === 'expense' ? 'selected' : '' ?>><?= esc(lang('Finance.type_expense')) ?></option>
                        <option value="income" <?= old('type', $category['type'] ?? '') === 'income' ? 'selected' : '' ?>><?= esc(lang('Finance.type_income')) ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="color"><?= esc(lang('Finance.color')) ?></label>
                    <input type="color" id="color" name="color" value="<?= esc(old('color', $category['color'] ?? '#64748b')) ?>">
                </div>
            </div>
            <div class="form-actions">
                <a href="<?= site_url('module/finance/categories') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a>
                <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
            </div>
        </form>
    </div>
</div>
</div>
<?= $this->endSection() ?>
