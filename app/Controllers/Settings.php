<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasSettingsNav;
use App\Models\TenantModel;

class Settings extends BaseController
{
    use HasSettingsNav;

    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canViewSettings()) {
            return $tenant === null ? redirect()->to('/dashboard') : $this->settingsDeniedRedirect();
        }

        return $this->render('settings/index', [
            'title'          => lang('Settings.title'),
            'moduleNav'      => 'general',
            'moduleNavItems' => $this->settingsNavItems(),
            'tenant'         => $tenant,
            'canManage'      => $this->canManageSettings(),
            'breadcrumbs'    => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Settings.title')],
            ],
        ]);
    }

    public function update()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $tenant === null ? redirect()->to('/dashboard') : $this->settingsDeniedRedirect();
        }

        $rules = [
            'name'               => 'required|max_length[191]',
            'timezone'           => 'required|max_length[60]',
            'currency'           => 'required|max_length[10]',
            'fiscal_year_start'  => 'required|integer|greater_than[0]|less_than[13]',
            'economic_code'      => 'permit_empty|max_length[20]',
            'national_id'        => 'permit_empty|max_length[20]',
            'approval_threshold' => 'permit_empty|decimal',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(TenantModel::class)->update((int) $tenant['id'], [
            'name'               => (string) $this->request->getPost('name'),
            'timezone'           => (string) $this->request->getPost('timezone'),
            'currency'           => (string) $this->request->getPost('currency'),
            'fiscal_year_start'  => (int) $this->request->getPost('fiscal_year_start'),
            'economic_code'      => (string) $this->request->getPost('economic_code'),
            'national_id'        => (string) $this->request->getPost('national_id'),
            'vat_registered'     => $this->request->getPost('vat_registered') ? 1 : 0,
            'approval_threshold' => (float) ($this->request->getPost('approval_threshold') ?: 10000000),
        ]);

        return redirect()->to('/module/settings')->with('success', lang('Settings.saved'));
    }
}
