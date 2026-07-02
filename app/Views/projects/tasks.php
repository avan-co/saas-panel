<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/project_subnav') ?>
<div class="page-header card-header-row">
    <h2 class="page-heading"><?= esc($project['name']) ?> — <?= esc(lang('Projects.tasks')) ?> (<?= esc($progress) ?>%)</h2>
    <div>
        <a href="<?= site_url('module/projects/' . $project['id'] . '/tasks?view=list') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('Projects.list_view')) ?></a>
        <a href="<?= site_url('module/projects/' . $project['id'] . '/tasks') ?>" class="btn btn-ghost btn-sm"><?= esc(lang('Projects.kanban_view')) ?></a>
    </div>
</div>
<?php if ($canEdit): ?>
<div class="card form-card"><div class="card-body">
<form method="post" action="<?= site_url('module/projects/' . $project['id'] . '/tasks/store') ?>" class="app-form"><?= csrf_field() ?>
<div class="form-row">
    <div class="form-group"><label><?= esc(lang('Projects.task_title')) ?></label><input type="text" name="title" required></div>
    <div class="form-group"><label><?= esc(lang('Projects.assignee')) ?></label>
        <select name="assignee_user_id"><option value="">—</option>
        <?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>"><?= esc($u['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label><?= esc(lang('Projects.due_date')) ?></label><input type="text" name="due_date" class="jalali-date"></div>
</div>
<button type="submit" class="btn btn-primary"><?= esc(lang('Projects.add_task')) ?></button>
</form></div></div>
<?php endif; ?>
<div class="kanban-board kanban-board-6" id="kanbanBoard" data-project="<?= $project['id'] ?>">
<?php foreach (['backlog','todo','doing','review','testing','done'] as $key): ?>
<div class="kanban-column" data-status="<?= $key ?>">
<div class="card"><div class="card-header"><h3><?= esc(lang('Projects.status_' . $key)) ?></h3></div>
<div class="card-body kanban-dropzone" data-status="<?= $key ?>">
<?php foreach ($columns[$key] as $task): ?>
<div class="kanban-card" draggable="<?= $canEdit ? 'true' : 'false' ?>" data-task-id="<?= $task['id'] ?>">
<a href="<?= site_url('module/projects/' . $project['id'] . '/tasks/' . $task['id']) ?>"><strong><?= esc($task['title']) ?></strong></a>
<?php if (! empty($task['due_date'])): ?><div class="text-muted text-sm"><?= esc(jalali_date($task['due_date'])) ?></div><?php endif; ?>
</div>
<?php endforeach; ?>
</div></div></div>
<?php endforeach; ?>
</div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function(){
  var board=document.getElementById('kanbanBoard'); if(!board) return;
  var projectId=board.dataset.project, csrf=document.querySelector('input[name="csrf_test_name"]')?.value;
  board.querySelectorAll('.kanban-card[draggable="true"]').forEach(function(card){
    card.addEventListener('dragstart',function(e){ e.dataTransfer.setData('text/plain',card.dataset.taskId); });
  });
  board.querySelectorAll('.kanban-dropzone').forEach(function(zone){
    zone.addEventListener('dragover',function(e){ e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave',function(){ zone.classList.remove('drag-over'); });
    zone.addEventListener('drop',function(e){
      e.preventDefault(); zone.classList.remove('drag-over');
      var taskId=e.dataTransfer.getData('text/plain'), status=zone.dataset.status;
      var fd=new FormData(); fd.append('status',status); if(csrf) fd.append('csrf_test_name',csrf);
      fetch('/module/projects/'+projectId+'/tasks/'+taskId+'/status',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ if(r.ok) location.reload(); });
    });
  });
})();
</script>
<?= $this->endSection() ?>
