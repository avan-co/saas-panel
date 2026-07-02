<?php $steps = [
    1 => lang('Install.step_requirements'),
    2 => lang('Install.step_database'),
    3 => lang('Install.step_setup'),
    4 => lang('Install.step_process'),
]; ?>
<div class="install-steps">
    <?php foreach ($steps as $num => $label): ?>
        <div class="install-step <?= ($step ?? 1) > $num ? 'completed' : '' ?> <?= ($step ?? 1) >= $num ? 'active' : '' ?> <?= ($step ?? 1) === $num ? 'current' : '' ?>">
            <span class="step-num"><?= $num ?></span>
            <span class="step-label"><?= esc($label) ?></span>
        </div>
    <?php endforeach; ?>
</div>
