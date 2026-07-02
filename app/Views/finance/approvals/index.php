<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Finance.approvals')) ?></h2></div>
<?php if ($requests === []): ?>
    <?= view('partials/empty_state', ['message' => lang('Finance.no_pending_approvals')]) ?>
<?php else: ?>
<div class="table-wrap"><table class="data-table">
<thead><tr><th><?= esc(lang('Finance.date')) ?></th><th><?= esc(lang('Finance.amount')) ?></th><th><?= esc(lang('Finance.description')) ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($requests as $req): $txn = $req['transaction'] ?? null; ?>
<tr>
    <td><?= esc($req['created_at'] ?? '') ?></td>
    <td><?= esc(number_format((float) $req['amount'], 0)) ?></td>
    <td><?= esc($txn['description'] ?? '—') ?></td>
    <td class="actions-cell">
        <?php if ($canApprove): ?>
        <form method="post" action="<?= site_url('module/finance/approvals/' . $req['id'] . '/approve') ?>" class="inline-form"><?= csrf_field() ?><button type="submit" class="btn btn-primary btn-sm"><?= esc(lang('Finance.approve')) ?></button></form>
        <form method="post" action="<?= site_url('module/finance/approvals/' . $req['id'] . '/reject') ?>" class="inline-form"><?= csrf_field() ?><button type="submit" class="btn btn-secondary btn-sm"><?= esc(lang('Finance.reject')) ?></button></form>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
