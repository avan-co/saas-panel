<?php if (! empty($breadcrumbs)): ?>
<nav class="breadcrumb" aria-label="Breadcrumb">
    <?php foreach ($breadcrumbs as $i => $crumb): ?>
        <?php if ($i > 0): ?>
            <span class="breadcrumb-sep" aria-hidden="true">/</span>
        <?php endif; ?>
        <?php if (! empty($crumb['url'])): ?>
            <a href="<?= esc($crumb['url']) ?>"><?= esc($crumb['label']) ?></a>
        <?php else: ?>
            <span class="breadcrumb-current"><?= esc($crumb['label']) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
<?php endif; ?>
