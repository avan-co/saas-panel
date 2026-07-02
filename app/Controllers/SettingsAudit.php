<?php

namespace App\Controllers;

use App\Models\AuditLogModel;

class SettingsAudit extends BaseController
{
    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManage()) {
            return redirect()->to('/module/settings')->with('error', lang('Settings.no_permission'));
        }

        return $this->render('settings/audit', [
            'title'          => lang('Settings.audit_log'),
            'moduleNav'      => 'audit',
            'moduleNavItems' => config('ModuleMenus')->settings,
            'logs'           => model(AuditLogModel::class)->getForTenant((int) $tenant['id'], 200),
            'breadcrumbs'    => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Settings.title'), 'url' => site_url('module/settings')],
                ['label' => lang('Settings.audit_log')],
            ],
        ]);
    }

    protected function canManage(): bool
    {
        if (session('is_platform_admin')) {
            return true;
        }

        $tenantId = (int) (service('tenantContext')->getTenant()['id'] ?? 0);
        $row      = model(\App\Models\TenantMembershipModel::class)
            ->where('tenant_id', $tenantId)
            ->where('user_id', (int) session('user_id'))
            ->first();

        return $row !== null && in_array($row['role'], ['owner', 'admin'], true);
    }
}
