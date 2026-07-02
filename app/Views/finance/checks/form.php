<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc($check ? lang('Finance.edit_check') : lang('Finance.new_check')) ?></h2></div>
<div class="card form-card"><div class="card-body">
<form method="post" action="<?= $check ? site_url('module/finance/checks/' . $check['id'] . '/update') : site_url('module/finance/checks/store') ?>" class="app-form">
<?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.direction')) ?></label>
        <select name="direction"><?php foreach (['received','payable'] as $d): ?><option value="<?= $d ?>" <?= old('direction', $check['direction'] ?? 'payable') === $d ? 'selected' : '' ?>><?= esc(lang('Finance.direction_' . $d)) ?></option><?php endforeach; ?></select>
    </div>
    <div class="form-group"><label><?= esc(lang('Finance.check_number')) ?></label><input type="text" name="check_number" value="<?= esc(old('check_number', $check['check_number'] ?? '')) ?>" required></div>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.bank')) ?></label><input type="text" name="bank" value="<?= esc(old('bank', $check['bank'] ?? '')) ?>"></div>
    <div class="form-group"><label><?= esc(lang('Finance.amount')) ?></label><input type="number" name="amount" min="1" value="<?= esc(old('amount', $check['amount'] ?? '')) ?>" required></div>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.due_date')) ?></label><input type="text" name="due_date" class="jalali-date" value="<?= esc(old('due_date', isset($check) ? jalali_date($check['due_date']) : today_for_input($locale ?? 'fa'))) ?>" required></div>
    <div class="form-group"><label><?= esc(lang('App.status')) ?></label>
        <select name="status"><?php foreach (['pending','deposited','cleared','bounced','paid'] as $s): ?><option value="<?= $s ?>" <?= old('status', $check['status'] ?? 'pending') === $s ? 'selected' : '' ?>><?= esc(lang('Finance.check_status_' . $s)) ?></option><?php endforeach; ?></select>
    </div>
</div>
<div class="form-actions"><a href="<?= site_url('module/finance/checks') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a><button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button></div>
</form></div></div>
</div>
<?= $this->endSection() ?>
