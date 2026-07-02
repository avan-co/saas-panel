<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header">
    <div class="page-header-text"><h2 class="page-heading"><?= esc(lang('Finance.assets')) ?></h2></div>
    <a href="<?= site_url('module/finance/assets/new') ?>" class="btn btn-primary"><?= esc(lang('Finance.new_asset')) ?></a>
</div>
<div class="card card-elevated"><div class="table-wrap">
<?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
<table class="data-table">
<thead><tr><th><?= esc(lang('Finance.asset_name')) ?></th><th><?= esc(lang('Finance.category')) ?></th><th><?= esc(lang('Finance.custodian')) ?></th><th><?= esc(lang('Finance.purchase_price')) ?></th><th><?= esc(lang('App.status')) ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($assets as $asset): ?>
<tr>
    <td><?= esc($asset['name']) ?></td>
    <td><?= esc($asset['category'] ?? '—') ?></td>
    <td><?= esc($asset['custodian'] ?? '—') ?></td>
    <td class="amount-cell"><?= esc($fmt((float) $asset['purchase_price'])) ?></td>
    <td><span class="badge"><?= esc(lang('Finance.asset_status_' . $asset['status'])) ?></span></td>
    <td class="table-actions">
        <a href="<?= site_url('module/finance/assets/' . $asset['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
        <?= view('partials/delete_form', ['action' => site_url('module/finance/assets/' . $asset['id'] . '/delete')]) ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
</div>
<?= $this->endSection() ?>
