<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasSettingsNav;
use App\Models\PeriodLockModel;

class SettingsPeriodLock extends BaseController
{
    use HasSettingsNav;

    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $tenant === null ? redirect()->to('/dashboard') : $this->settingsDeniedRedirect();
        }

        return $this->render('settings/period_locks', [
            'title'          => lang('Settings.period_locks'),
            'moduleNav'      => 'period_locks',
            'moduleNavItems' => $this->settingsNavItems(),
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

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        $year  = (int) $this->request->getPost('year');
        $month = (int) $this->request->getPost('month');

        if ($year < 2000 || $month < 1 || $month > 12) {
            return redirect()->back()->with('error', lang('Settings.period_invalid'));
        }

        service('periodLock')->lock((int) $tenant['id'], $year, $month, (int) session('user_id'));

        return redirect()->to('/module/settings/period-locks')->with('success', lang('Settings.period_locked'));
    }
}
