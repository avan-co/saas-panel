<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3><?= esc($title) ?></h3>
    </div>
    <div class="card-body empty-state coming-soon">
        <div class="coming-soon-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="10"/></svg>
        </div>
        <h4><?= esc($title) ?></h4>
        <p><?= esc(lang('App.coming_soon')) ?></p>
    </div>
</div>
<?= $this->endSection() ?>
