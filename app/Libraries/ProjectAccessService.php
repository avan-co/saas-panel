<?php

namespace App\Libraries;

use App\Models\ProjectMemberModel;
use App\Models\ProjectModel;
use App\Models\ProjectTeamModel;

class ProjectAccessService
{
    public function hasFullAccess(int $userId, int $tenantId): bool
    {
        if (session('is_platform_admin')) {
            return true;
        }

        $perms = service('permissions');
        $perms->load($userId, $tenantId);
        $membership = $perms->membership();

        if ($membership === null) {
            return false;
        }

        $role = $membership['role'] ?? 'viewer';

        if (in_array($role, ['owner', 'admin'], true)) {
            return true;
        }

        return $perms->can('projects.manage');
    }

    public function accessibleProjectIds(int $userId, int $tenantId): ?array
    {
        if ($this->hasFullAccess($userId, $tenantId)) {
            return null;
        }

        $ids = [];

        $memberRows = model(ProjectMemberModel::class)
            ->select('project_id')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->findAll();

        foreach ($memberRows as $row) {
            $ids[] = (int) $row['project_id'];
        }

        $managerRows = model(ProjectModel::class)
            ->select('id')
            ->where('tenant_id', $tenantId)
            ->where('manager_user_id', $userId)
            ->findAll();

        foreach ($managerRows as $row) {
            $ids[] = (int) $row['id'];
        }

        foreach (model(ProjectTeamModel::class)->projectIdsForUser($userId, $tenantId) as $projectId) {
            $ids[] = $projectId;
        }

        return array_values(array_unique($ids));
    }

    public function canAccessProject(int $userId, int $tenantId, int $projectId): bool
    {
        $allowed = $this->accessibleProjectIds($userId, $tenantId);

        return $allowed === null || in_array($projectId, $allowed, true);
    }

    public function filterProjects(int $userId, int $tenantId, array $projects): array
    {
        $allowed = $this->accessibleProjectIds($userId, $tenantId);

        if ($allowed === null) {
            return $projects;
        }

        return array_values(array_filter(
            $projects,
            static fn ($p) => in_array((int) $p['id'], $allowed, true),
        ));
    }
}
