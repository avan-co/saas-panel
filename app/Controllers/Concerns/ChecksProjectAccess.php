<?php

namespace App\Controllers\Concerns;

trait ChecksProjectAccess
{
    use ChecksPermission;

    protected function requireProjectAccess(int $projectId): bool
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return false;
        }

        return service('projectAccess')->canAccessProject(
            (int) session('user_id'),
            (int) $tenant['id'],
            $projectId,
        );
    }

    protected function projectAccessDeniedRedirect(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to('/module/projects')->with('error', lang('Projects.access_denied'));
    }
}
