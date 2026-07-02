<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
$moduleMeta = [
    'payroll'   => ['icon' => 'users', 'color' => '#8b5cf6'],
    'insurance' => ['icon' => 'shield', 'color' => '#06b6d4'],
    'tax'       => ['icon' => 'file', 'color' => '#f59e0b'],
    'projects'  => ['icon' => 'folder', 'color' => '#3b82f6'],
    'settings'  => ['icon' => 'settings', 'color' => '#64748b'],
];
$meta = $moduleMeta[$moduleCode] ?? ['icon' => 'grid', 'color' => '#3b82f6'];
?>

<div class="module-placeholder">
    <div class="module-placeholder-card">
        <div class="module-placeholder-icon" style="--module-color: <?= esc($meta['color']) ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 6v6l4 2"/>
            </svg>
        </div>
        <h2><?= esc($title) ?></h2>
        <p><?= esc(lang('App.coming_soon')) ?></p>
        <span class="module-placeholder-badge"><?= esc(lang('App.modules.' . $moduleCode)) ?></span>
        <a href="<?= site_url('dashboard') ?>" class="btn btn-secondary"><?= esc(lang('App.back')) ?></a>
    </div>
</div>
<?= $this->endSection() ?>
