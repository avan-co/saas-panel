<?php

namespace App\Controllers\Concerns;

trait ChecksPermission
{
    protected function requirePermission(string $permission): bool
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return false;
        }

        $perms = service('permissions');
        $perms->load((int) session('user_id'), (int) $tenant['id']);

        return $perms->can($permission);
    }

    protected function permissionDeniedRedirect(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->back()->with('error', lang('App.permission_denied'));
    }
}
