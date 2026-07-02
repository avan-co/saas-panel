<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>

<div class="page-header">
    <div class="page-header-text"><h2 class="page-heading"><?= esc(lang('Finance.accounts')) ?></h2></div>
    <a href="<?= site_url('module/finance/accounts/new') ?>" class="btn btn-primary"><?= esc(lang('Finance.new_account')) ?></a>
</div>

<div class="card card-elevated">
    <div class="table-wrap">
        <?php if ($accounts === []): ?>
            <?= view('partials/empty_state', ['message' => lang('Finance.no_accounts'), 'actionUrl' => site_url('module/finance/accounts/new'), 'actionLabel' => lang('Finance.new_account')]) ?>
        <?php else: ?>
            <?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('Finance.account')) ?></th>
                        <th><?= esc(lang('Finance.account_type')) ?></th>
                        <th><?= esc(lang('Finance.amount')) ?></th>
                        <th><?= esc(lang('App.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td><?= esc($account['name']) ?></td>
                            <td><?= esc(lang('Finance.account_type_' . $account['type'])) ?></td>
                            <td class="amount-cell"><?= esc($fmt((float) $account['balance'])) ?> <?= esc($account['currency']) ?></td>
                            <td class="table-actions">
                                <a href="<?= site_url('module/finance/accounts/' . $account['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
                                <?= view('partials/delete_form', ['action' => site_url('module/finance/accounts/' . $account['id'] . '/delete')]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</div>
<?= $this->endSection() ?>
