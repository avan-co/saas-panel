<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>

<div class="page-header">
    <div class="page-header-text"><h2 class="page-heading"><?= esc(lang('Finance.categories')) ?></h2></div>
    <a href="<?= site_url('module/finance/categories/new') ?>" class="btn btn-primary"><?= esc(lang('Finance.new_category')) ?></a>
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-header"><h3><?= esc(lang('Finance.income_categories')) ?></h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <tbody>
                    <?php foreach ($income as $cat): ?>
                        <tr>
                            <td><span class="tag" style="--tag-color:<?= esc($cat['color']) ?>"><?= esc($cat['name']) ?></span></td>
                            <td class="table-actions">
                                <a href="<?= site_url('module/finance/categories/' . $cat['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
                                <?= view('partials/delete_form', ['action' => site_url('module/finance/categories/' . $cat['id'] . '/delete')]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3><?= esc(lang('Finance.expense_categories')) ?></h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <tbody>
                    <?php foreach ($expense as $cat): ?>
                        <tr>
                            <td><span class="tag" style="--tag-color:<?= esc($cat['color']) ?>"><?= esc($cat['name']) ?></span></td>
                            <td class="table-actions">
                                <a href="<?= site_url('module/finance/categories/' . $cat['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
                                <?= view('partials/delete_form', ['action' => site_url('module/finance/categories/' . $cat['id'] . '/delete')]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
