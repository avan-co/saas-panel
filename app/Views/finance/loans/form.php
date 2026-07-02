<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc($loan ? lang('Finance.edit_loan') : lang('Finance.new_loan')) ?></h2></div>
<div class="card form-card"><div class="card-body">
<form method="post" action="<?= $loan ? site_url('module/finance/loans/' . $loan['id'] . '/update') : site_url('module/finance/loans/store') ?>" class="app-form">
<?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.bank')) ?></label><input type="text" name="bank" value="<?= esc(old('bank', $loan['bank'] ?? '')) ?>" required></div>
    <div class="form-group"><label><?= esc(lang('Finance.principal')) ?></label><input type="number" name="principal" min="1" value="<?= esc(old('principal', $loan['principal'] ?? '')) ?>" required></div>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.interest_rate')) ?></label><input type="number" step="0.01" name="interest_rate" value="<?= esc(old('interest_rate', $loan['interest_rate'] ?? '0')) ?>"></div>
    <div class="form-group"><label><?= esc(lang('Finance.installment_amount')) ?></label><input type="number" name="installment_amount" min="1" value="<?= esc(old('installment_amount', $loan['installment_amount'] ?? '')) ?>" required></div>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.total_installments')) ?></label><input type="number" name="total_installments" min="1" value="<?= esc(old('total_installments', $loan['total_installments'] ?? '')) ?>" required></div>
    <div class="form-group"><label><?= esc(lang('Finance.paid_installments')) ?></label><input type="number" name="paid_installments" min="0" value="<?= esc(old('paid_installments', $loan['paid_installments'] ?? '0')) ?>"></div>
</div>
<div class="form-group"><label><?= esc(lang('Finance.start_date')) ?></label><input type="text" name="start_date" class="jalali-date" value="<?= esc(old('start_date', ! empty($loan['start_date']) ? jalali_date($loan['start_date']) : today_for_input($locale ?? 'fa'))) ?>"></div>
<div class="form-group"><label><?= esc(lang('App.status')) ?></label>
    <select name="status"><?php foreach (['active','paid','defaulted'] as $s): ?><option value="<?= $s ?>" <?= old('status', $loan['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= esc(lang('Finance.loan_status_' . $s)) ?></option><?php endforeach; ?></select>
</div>
<div class="form-actions"><a href="<?= site_url('module/finance/loans') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a><button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button></div>
</form></div></div>
</div>
<?= $this->endSection() ?>
