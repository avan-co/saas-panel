<?php
$tabs = $moduleTabs ?? [];
$active = $moduleNav ?? '';
?>
<?php if ($tabs !== []): ?>
<nav class="module-tabs">
    <?php foreach ($tabs as $tab): ?>
        <a href="<?= esc($tab['url']) ?>" class="module-tab <?= ($tab['key'] ?? '') === $active ? 'active' : '' ?>">
            <?= esc($tab['label']) ?>
        </a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>
