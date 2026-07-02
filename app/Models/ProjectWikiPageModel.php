<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectWikiPageModel extends Model
{
    protected $table         = 'project_wiki_pages';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'project_id', 'title', 'content', 'created_by'];
    protected $useTimestamps = true;

    public function forProject(int $tenantId, int $projectId): array
    {
        return $this->where('tenant_id', $tenantId)->where('project_id', $projectId)
            ->orderBy('title', 'ASC')->findAll();
    }
}
