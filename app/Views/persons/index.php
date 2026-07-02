<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-settings">
<?= $this->include('partials/breadcrumb') ?>
<div class="page-header">
    <h2 class="page-heading"><?= esc(lang('Persons.title')) ?></h2>
    <?php if (service('tenantContext')->getTenant()): ?>
    <a href="<?= site_url('module/persons/new') ?>" class="btn btn-primary"><?= esc(lang('Persons.new')) ?></a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= esc(lang('Persons.name')) ?></th>
                    <th><?= esc(lang('Persons.roles')) ?></th>
                    <th><?= esc(lang('Persons.phone')) ?></th>
                    <th><?= esc(lang('Persons.email')) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($persons as $p): ?>
                <tr>
                    <td><?= esc($p['name']) ?></td>
                    <td>
                        <?php foreach ($p['roles'] as $role): ?>
                            <span class="badge"><?= esc(lang('Persons.role_' . $role)) ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td dir="ltr"><?= esc($p['phone'] ?? '—') ?></td>
                    <td dir="ltr"><?= esc($p['email'] ?? '—') ?></td>
                    <td class="table-actions">
                        <a href="<?= site_url('module/persons/' . $p['id'] . '/edit') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('App.edit')) ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?= $this->endSection() ?>
