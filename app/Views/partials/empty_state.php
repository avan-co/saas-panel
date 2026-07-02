<?php
/** @var string $message */
/** @var string|null $actionUrl */
/** @var string|null $actionLabel */
?>
<div class="empty-state empty-state-action">
    <p><?= esc($message) ?></p>
    <?php if (! empty($actionUrl) && ! empty($actionLabel)): ?>
        <a href="<?= esc($actionUrl, 'attr') ?>" class="btn btn-primary btn-sm"><?= esc($actionLabel) ?></a>
    <?php endif; ?>
</div>
