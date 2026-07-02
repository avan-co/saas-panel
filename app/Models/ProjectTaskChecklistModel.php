<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectTaskChecklistModel extends Model
{
    protected $table         = 'project_task_checklist';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['task_id', 'tenant_id', 'title', 'is_done', 'sort_order'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function forTask(int $taskId): array
    {
        return $this->where('task_id', $taskId)->orderBy('sort_order', 'ASC')->findAll();
    }
}
