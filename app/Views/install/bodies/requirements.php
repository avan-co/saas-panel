<p class="install-help"><?= esc(lang('Install.requirements_help')) ?></p>

<?php if (! empty($isReinstall)): ?>
    <div class="alert alert-warning install-reinstall-notice">
        <?= esc(lang('Install.reinstall_warning')) ?>
    </div>
<?php endif; ?>

<ul class="requirements-list">
    <?php foreach ($requirements as $req): ?>
        <li class="<?= $req['passed'] ? 'passed' : 'failed' ?>">
            <span class="req-icon"><?= $req['passed'] ? '✓' : '✗' ?></span>
            <span><?= esc($req['label']) ?></span>
            <span class="req-status"><?= esc($req['passed'] ? lang('Install.passed') : lang('Install.failed')) ?></span>
        </li>
    <?php endforeach; ?>
</ul>

<div class="install-actions">
    <?php if ($canContinue): ?>
        <a href="<?= site_url('install/database') ?>" class="btn btn-primary"><?= esc(lang('Install.next')) ?></a>
    <?php else: ?>
        <button type="button" class="btn btn-primary" disabled><?= esc(lang('Install.next')) ?></button>
    <?php endif; ?>
</div>
