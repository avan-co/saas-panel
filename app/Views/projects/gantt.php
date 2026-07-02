<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/project_subnav') ?>
<h2 class="page-heading"><?= esc(lang('Projects.gantt')) ?></h2>
<div class="gantt-chart" style="overflow-x:auto;padding:20px 0">
<?php
$minDate = $project['start_date'] ?? date('Y-m-d');
$maxDate = $project['end_date'] ?? date('Y-m-d', strtotime('+60 days'));
$range = max(1, (strtotime($maxDate) - strtotime($minDate)) / 86400);
?>
<?php foreach ($tasks as $task):
    $start = $task['start_date'] ?? $task['due_date'] ?? $minDate;
    $end   = $task['due_date'] ?? $start;
    $offset = max(0, (strtotime((string)$start) - strtotime($minDate)) / 86400);
    $width  = max(2, ((strtotime((string)$end) - strtotime((string)$start)) / 86400) + 1);
    $leftPct = ($offset / $range) * 100;
    $widthPct = min(100 - $leftPct, ($width / $range) * 100);
?>
<div class="gantt-row" style="display:flex;align-items:center;margin-bottom:8px">
    <div style="width:180px;flex-shrink:0;font-size:13px"><?= esc($task['title']) ?></div>
    <div style="flex:1;position:relative;height:28px;background:var(--surface-2);border-radius:4px">
        <div class="gantt-bar status-<?= esc($task['status']) ?>" style="position:absolute;left:<?= $leftPct ?>%;width:<?= max(2,$widthPct) ?>%;height:100%;border-radius:4px;background:var(--primary);opacity:0.85" title="<?= esc($task['status']) ?>"></div>
    </div>
</div>
<?php endforeach; ?>
<?php foreach ($milestones as $ms): if (empty($ms['due_date'])) continue;
    $offset = max(0, (strtotime((string)$ms['due_date']) - strtotime($minDate)) / 86400);
    $leftPct = ($offset / $range) * 100;
?>
<div class="text-muted text-sm" style="margin-left:180px">◆ <?= esc($ms['title']) ?> — <?= esc(jalali_date($ms['due_date'])) ?></div>
<?php endforeach; ?>
</div>
</div>
<?= $this->endSection() ?>
