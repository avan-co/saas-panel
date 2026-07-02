<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc($asset ? lang('Finance.edit_asset') : lang('Finance.new_asset')) ?></h2></div>
<div class="card form-card"><div class="card-body">
<form method="post" action="<?= $asset ? site_url('module/finance/assets/' . $asset['id'] . '/update') : site_url('module/finance/assets/store') ?>" class="app-form">
<?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.asset_name')) ?></label><input type="text" name="name" value="<?= esc(old('name', $asset['name'] ?? '')) ?>" required></div>
    <div class="form-group"><label><?= esc(lang('Finance.category')) ?></label><input type="text" name="category" value="<?= esc(old('category', $asset['category'] ?? '')) ?>" placeholder="مثلاً پرینتر سه‌بعدی"></div>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.purchase_price')) ?></label><input type="number" name="purchase_price" min="0" value="<?= esc(old('purchase_price', $asset['purchase_price'] ?? '')) ?>" required></div>
    <div class="form-group"><label><?= esc(lang('Finance.purchase_date')) ?></label><input type="text" name="purchase_date" class="jalali-date" value="<?= esc(old('purchase_date', ! empty($asset['purchase_date']) ? jalali_date($asset['purchase_date']) : today_for_input($locale ?? 'fa'))) ?>"></div>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.custodian')) ?></label><input type="text" name="custodian" value="<?= esc(old('custodian', $asset['custodian'] ?? '')) ?>"></div>
    <div class="form-group"><label><?= esc(lang('Finance.location')) ?></label><input type="text" name="location" value="<?= esc(old('location', $asset['location'] ?? '')) ?>"></div>
</div>
<div class="form-group"><label><?= esc(lang('Finance.serial_number')) ?></label><input type="text" name="serial_number" value="<?= esc(old('serial_number', $asset['serial_number'] ?? '')) ?>" dir="ltr"></div>
<div class="form-group"><label><?= esc(lang('App.status')) ?></label>
    <select name="status"><?php foreach (['active','disposed'] as $s): ?><option value="<?= $s ?>" <?= old('status', $asset['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= esc(lang('Finance.asset_status_' . $s)) ?></option><?php endforeach; ?></select>
</div>
<div class="form-actions"><a href="<?= site_url('module/finance/assets') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a><button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button></div>
</form></div></div>
</div>
<?= $this->endSection() ?>
