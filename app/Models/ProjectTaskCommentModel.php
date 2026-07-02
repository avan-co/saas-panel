<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectTaskCommentModel extends Model
{
    protected $table         = 'project_task_comments';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['task_id', 'tenant_id', 'user_id', 'parent_id', 'body'];
    protected $useTimestamps = true;

    public function forTask(int $taskId): array
    {
        return $this->select('project_task_comments.*, users.name AS user_name')
            ->join('users', 'users.id = project_task_comments.user_id')
            ->where('task_id', $taskId)
            ->orderBy('project_task_comments.created_at', 'ASC')
            ->findAll();
    }
}
