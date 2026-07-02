<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasSettingsNav;
use App\Models\AuditLogModel;

class SettingsAudit extends BaseController
{
    use HasSettingsNav;

    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $tenant === null ? redirect()->to('/dashboard') : $this->settingsDeniedRedirect();
        }

        return $this->render('settings/audit', [
            'title'          => lang('Settings.audit_log'),
            'moduleNav'      => 'audit',
            'moduleNavItems' => $this->settingsNavItems(),
            'logs'           => model(AuditLogModel::class)->getForTenant((int) $tenant['id'], 200),
            'breadcrumbs'    => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Settings.title'), 'url' => site_url('module/settings')],
                ['label' => lang('Settings.audit_log')],
            ],
        ]);
    }
}
