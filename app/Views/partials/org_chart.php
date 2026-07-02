<?php
$renderNode = static function (array $node, int $depth = 0) use (&$renderNode): void {
    $indent = str_repeat('— ', $depth);
    ?>
    <div class="org-node" style="margin-inline-start: <?= $depth * 16 ?>px">
        <strong><?= esc($node['name']) ?></strong>
        <span class="badge"><?= esc(lang('Settings.role_' . $node['role'])) ?></span>
        <?php if (! empty($node['department'])): ?><span class="text-muted"><?= esc($node['department']) ?></span><?php endif; ?>
    </div>
    <?php foreach ($node['children'] ?? [] as $child): ?>
        <?php $renderNode($child, $depth + 1); ?>
    <?php endforeach;
};

if ($orgTree !== []): ?>
    <div class="org-chart card" style="margin-bottom:20px">
        <div class="card-header"><h3><?= esc(lang('Settings.org_chart')) ?></h3></div>
        <div class="card-body"><?php foreach ($orgTree as $root) { $renderNode($root); } ?></div>
    </div>
<?php endif; ?>
