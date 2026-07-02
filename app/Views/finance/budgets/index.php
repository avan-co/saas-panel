<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Finance.budgets')) ?></h2></div>

<div class="card card-elevated form-card" style="margin-bottom:20px">
    <div class="card-header"><h3><?= esc(lang('Finance.set_budget')) ?></h3></div>
    <div class="card-body">
        <form method="post" action="<?= site_url('module/finance/budgets/store') ?>" class="app-form">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group">
                    <label><?= esc(lang('Finance.category')) ?></label>
                    <select name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= esc($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?= esc(lang('Finance.amount')) ?></label>
                    <input type="number" name="amount" min="1" required>
                </div>
            </div>
            <input type="hidden" name="year" value="<?= (int) $year ?>">
            <input type="hidden" name="month" value="<?= (int) $month ?>">
            <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
        <table class="data-table">
            <thead><tr><th><?= esc(lang('Finance.category')) ?></th><th><?= esc(lang('Finance.budget')) ?></th><th><?= esc(lang('App.actions')) ?></th></tr></thead>
            <tbody>
                <?php foreach ($budgets as $b): ?>
                    <tr>
                        <td><?= esc($b['category_name'] ?? '—') ?></td>
                        <td class="amount-cell"><?= esc($fmt((float) $b['amount'])) ?></td>
                        <td><?= view('partials/delete_form', ['action' => site_url('module/finance/budgets/' . $b['id'] . '/delete')]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?= $this->endSection() ?>
