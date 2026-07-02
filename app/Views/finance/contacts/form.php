<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc($contact ? lang('Finance.edit_contact') : lang('Finance.new_contact')) ?></h2></div>
<div class="card form-card"><div class="card-body">
<form method="post" action="<?= $contact ? site_url('module/finance/contacts/' . $contact['id'] . '/update') : site_url('module/finance/contacts/store') ?>" class="app-form">
<?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.contact')) ?></label><input type="text" name="name" value="<?= esc(old('name', $contact['name'] ?? '')) ?>" required></div>
    <div class="form-group"><label><?= esc(lang('Finance.contact_type')) ?></label>
        <select name="type" required>
            <?php foreach (['supplier','contractor','employee','customer','both'] as $t): ?>
                <option value="<?= $t ?>" <?= old('type', $contact['type'] ?? '') === $t ? 'selected' : '' ?>><?= esc(lang('Finance.contact_type_' . $t)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Finance.phone')) ?></label><input type="text" name="phone" value="<?= esc(old('phone', $contact['phone'] ?? '')) ?>"></div>
    <div class="form-group"><label><?= esc(lang('Finance.email')) ?></label><input type="email" name="email" value="<?= esc(old('email', $contact['email'] ?? '')) ?>" dir="ltr"></div>
</div>
<div class="form-group"><label><?= esc(lang('Finance.address')) ?></label><input type="text" name="address" value="<?= esc(old('address', $contact['address'] ?? '')) ?>"></div>
<div class="form-actions">
    <a href="<?= site_url('module/finance/contacts') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a>
    <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
</div>
</form>
</div></div>
</div>
<?= $this->endSection() ?>
