<?= $this->include('partials/breadcrumb') ?>

<?php if (! empty($moduleNavItems)): ?>
<nav class="module-subnav" aria-label="Module sections">
    <?php foreach ($moduleNavItems as $item): ?>
        <?php $active = ($moduleNav ?? '') === $item['key']; ?>
        <a href="<?= site_url($item['route']) ?>" class="module-subnav-item <?= $active ? 'active' : '' ?>">
            <?= esc(lang($item['label'])) ?>
        </a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>
