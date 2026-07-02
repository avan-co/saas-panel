<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header">
    <div class="page-header-text"><h2 class="page-heading"><?= esc(lang('Finance.contacts')) ?></h2></div>
    <a href="<?= site_url('module/finance/contacts/new') ?>" class="btn btn-primary"><?= esc(lang('Finance.new_contact')) ?></a>
</div>
<div class="card card-elevated">
    <div class="table-wrap">
        <?php if ($contacts === []): ?>
            <?= view('partials/empty_state', ['message' => lang('Finance.no_contacts'), 'actionUrl' => site_url('module/finance/contacts/new'), 'actionLabel' => lang('Finance.new_contact')]) ?>
        <?php else: ?>
            <?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
            <table class="data-table">
                <thead><tr><th><?= esc(lang('Finance.contact')) ?></th><th><?= esc(lang('Finance.contact_type')) ?></th><th><?= esc(lang('Finance.balance')) ?></th><th><?= esc(lang('App.actions')) ?></th></tr></thead>
                <tbody>
                    <?php foreach ($contacts as $c): ?>
                        <tr>
                            <td><a href="<?= site_url('module/finance/contacts/' . $c['id']) ?>"><?= esc($c['name']) ?></a></td>
                            <td><?= esc(lang('Finance.contact_type_' . $c['type'])) ?></td>
                            <td class="amount-cell"><?= esc($fmt((float) $c['balance'])) ?></td>
                            <td class="table-actions">
                                <a href="<?= site_url('module/finance/contacts/' . $c['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
                                <?= view('partials/delete_form', ['action' => site_url('module/finance/contacts/' . $c['id'] . '/delete')]) ?>
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
