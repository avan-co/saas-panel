<?php

namespace App\Controllers\Concerns;

trait ManagesTenantSettings
{
    protected function canViewSettings(): bool
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return false;
        }

        if (session('is_platform_admin')) {
            return true;
        }

        $perms = service('permissions');
        $perms->load((int) session('user_id'), (int) $tenant['id']);

        return $perms->can('settings.view') || $this->canManageSettings();
    }

    protected function canManageSettings(): bool
    {
        if (session('is_platform_admin')) {
            return true;
        }

        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return false;
        }

        $row = model(\App\Models\TenantMembershipModel::class)
            ->where('tenant_id', (int) $tenant['id'])
            ->where('user_id', (int) session('user_id'))
            ->first();

        return $row !== null && in_array($row['role'], ['owner', 'admin'], true);
    }

    protected function settingsDeniedRedirect(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to('/dashboard')->with('error', lang('Settings.no_permission'));
    }
}
