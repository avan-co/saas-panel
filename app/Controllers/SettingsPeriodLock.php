<?php

namespace App\Controllers;

use App\Models\PeriodLockModel;

class SettingsPeriodLock extends BaseController
{
    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManage()) {
            return redirect()->to('/module/settings')->with('error', lang('Settings.no_permission'));
        }

        return $this->render('settings/period_locks', [
            'title'          => lang('Settings.period_locks'),
            'moduleNav'      => 'period_locks',
            'moduleNavItems' => config('ModuleMenus')->settings,
            'locks'          => model(PeriodLockModel::class)->getForTenant((int) $tenant['id']),
            'breadcrumbs'    => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Settings.title'), 'url' => site_url('module/settings')],
                ['label' => lang('Settings.period_locks')],
            ],
        ]);
    }

    public function lock()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManage()) {
            return redirect()->to('/module/settings')->with('error', lang('Settings.no_permission'));
        }

        $year  = (int) $this->request->getPost('year');
        $month = (int) $this->request->getPost('month');

        if ($year < 2000 || $month < 1 || $month > 12) {
            return redirect()->back()->with('error', lang('Settings.period_invalid'));
        }

        service('periodLock')->lock((int) $tenant['id'], $year, $month, (int) session('user_id'));

        return redirect()->to('/module/settings/period-locks')->with('success', lang('Settings.period_locked'));
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
