<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-settings">
<?= $this->include('partials/breadcrumb') ?>
<div class="page-header"><h2 class="page-heading"><?= esc($person ? lang('Persons.edit') : lang('Persons.new')) ?></h2></div>

<div class="card form-card">
    <div class="card-body">
        <?php
        $action = $person ? site_url('module/persons/' . $person['id'] . '/update') : site_url('module/persons/store');
        $roles = ['employee', 'customer', 'supplier', 'contractor', 'shareholder'];
        ?>
        <form method="post" action="<?= $action ?>" class="app-form">
            <?= csrf_field() ?>
            <div class="form-group"><label><?= esc(lang('Persons.name')) ?></label><input type="text" name="name" value="<?= esc(old('name', $person['name'] ?? '')) ?>" required></div>
            <div class="form-row">
                <div class="form-group"><label><?= esc(lang('Persons.phone')) ?></label><input type="text" name="phone" value="<?= esc(old('phone', $person['phone'] ?? '')) ?>" dir="ltr"></div>
                <div class="form-group"><label><?= esc(lang('Persons.email')) ?></label><input type="email" name="email" value="<?= esc(old('email', $person['email'] ?? '')) ?>" dir="ltr"></div>
            </div>
            <div class="form-group"><label><?= esc(lang('Persons.national_id')) ?></label><input type="text" name="national_id" value="<?= esc(old('national_id', $person['national_id'] ?? '')) ?>" dir="ltr"></div>
            <div class="form-group"><label><?= esc(lang('Persons.address')) ?></label><input type="text" name="address" value="<?= esc(old('address', $person['address'] ?? '')) ?>"></div>
            <div class="form-group"><label><?= esc(lang('Persons.note')) ?></label><textarea name="note" rows="2"><?= esc(old('note', $person['note'] ?? '')) ?></textarea></div>
            <fieldset class="permissions-fieldset">
                <legend><?= esc(lang('Persons.roles')) ?></legend>
                <?php foreach ($roles as $role): ?>
                    <label class="checkbox-label"><input type="checkbox" name="roles[]" value="<?= $role ?>" <?= in_array($role, $person['roles'] ?? [], true) ? 'checked' : '' ?>> <?= esc(lang('Persons.role_' . $role)) ?></label>
                <?php endforeach; ?>
            </fieldset>
            <div class="form-actions">
                <a href="<?= site_url('module/persons') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a>
                <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
            </div>
        </form>
    </div>
</div>
</div>
<?= $this->endSection() ?>
