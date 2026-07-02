<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/project_subnav') ?>
<div class="page-header card-header-row">
    <h2 class="page-heading"><?= esc($project['name']) ?> — <?= esc(lang('Projects.timesheets')) ?></h2>
</div>

<?php if ($canEdit && $employees !== []): ?>
<div class="card form-card"><div class="card-body">
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/timesheets/store') ?>" class="app-form"><?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Payroll.employee')) ?></label>
        <select name="employee_id" required><option value="">—</option>
        <?php foreach ($employees as $emp): ?><option value="<?= $emp['id'] ?>"><?= esc($emp['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label><?= esc(lang('Projects.task_title')) ?></label>
        <select name="task_id"><option value="">—</option>
        <?php foreach ($tasks as $task): ?><option value="<?= $task['id'] ?>"><?= esc($task['title']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label><?= esc(lang('Projects.work_date')) ?></label><input type="text" name="work_date" class="jalali-date" required value="<?= esc(today_for_input($locale ?? 'fa')) ?>"></div>
</div>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Projects.start_time')) ?></label><input type="time" name="start_time"></div>
    <div class="form-group"><label><?= esc(lang('Projects.end_time')) ?></label><input type="time" name="end_time"></div>
    <div class="form-group"><label><?= esc(lang('Projects.hours')) ?></label><input type="number" name="hours" min="0.25" step="0.25" placeholder="8"></div>
</div>
<div class="form-group"><label><?= esc(lang('Finance.description')) ?></label><input type="text" name="note"></div>
<button type="submit" class="btn btn-primary"><?= esc(lang('Projects.add_timesheet')) ?></button>
</form></div></div>
<?php elseif ($canEdit): ?>
<div class="alert alert-warning"><?= esc(lang('Projects.timesheet_needs_payroll')) ?></div>
<?php endif; ?>

<div class="card card-elevated" style="margin-top:20px"><div class="card-header"><h3><?= esc(lang('Projects.timesheet_log')) ?></h3></div>
<div class="table-wrap">
<?php if ($timesheets === []): ?>
    <div class="card-body"><p class="text-muted"><?= esc(lang('Projects.no_timesheets')) ?></p></div>
<?php else: ?>
<table class="data-table">
<thead><tr><th><?= esc(lang('Projects.work_date')) ?></th><th><?= esc(lang('Payroll.employee')) ?></th><th><?= esc(lang('Projects.tasks')) ?></th><th><?= esc(lang('Projects.hours')) ?></th><th><?= esc(lang('Projects.labor_cost')) ?></th></tr></thead>
<tbody>
<?php foreach ($timesheets as $row): ?>
<tr>
    <td><?= esc(jalali_date($row['work_date'])) ?></td>
    <td><?= esc($row['employee_name']) ?></td>
    <td><?= esc($row['task_title'] ?? '—') ?></td>
    <td><?= esc($row['hours']) ?></td>
    <td class="amount-cell"><?= esc(format_amount((float) $row['labor_cost'])) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></div>
</div>
<?= $this->endSection() ?>
