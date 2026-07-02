<?= $this->include('partials/breadcrumb') ?>
<?php if (! empty($projectNavItems)): ?>
<nav class="module-subnav project-subnav" aria-label="Project sections">
    <?php foreach ($projectNavItems as $item): ?>
        <?php $active = ($projectNav ?? '') === $item['key']; ?>
        <a href="<?= site_url($item['route']) ?>" class="module-subnav-item <?= $active ? 'active' : '' ?>">
            <?= esc(lang($item['label'])) ?>
        </a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>
