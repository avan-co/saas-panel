<?php

namespace App\Controllers;

use App\Models\TenantModel;

class Settings extends BaseController
{
    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return redirect()->to('/dashboard');
        }

        return $this->render('settings/index', [
            'title'          => lang('Settings.title'),
            'moduleNav'      => 'general',
            'moduleNavItems' => config('ModuleMenus')->settings,
            'tenant'         => $tenant,
            'canManageUsers' => $this->canManageUsers(),
            'breadcrumbs'    => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Settings.title')],
            ],
        ]);
    }

    public function update()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return redirect()->to('/dashboard');
        }

        $rules = [
            'name'              => 'required|max_length[191]',
            'timezone'          => 'required|max_length[60]',
            'currency'          => 'required|max_length[10]',
            'fiscal_year_start' => 'required|integer|greater_than[0]|less_than[13]',
            'economic_code'     => 'permit_empty|max_length[20]',
            'national_id'       => 'permit_empty|max_length[20]',
            'approval_threshold'=> 'permit_empty|decimal',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(TenantModel::class)->update((int) $tenant['id'], [
            'name'              => (string) $this->request->getPost('name'),
            'timezone'          => (string) $this->request->getPost('timezone'),
            'currency'          => (string) $this->request->getPost('currency'),
            'fiscal_year_start' => (int) $this->request->getPost('fiscal_year_start'),
            'economic_code'     => (string) $this->request->getPost('economic_code'),
            'national_id'       => (string) $this->request->getPost('national_id'),
            'vat_registered'    => $this->request->getPost('vat_registered') ? 1 : 0,
            'approval_threshold'=> (float) ($this->request->getPost('approval_threshold') ?: 10000000),
        ]);

        return redirect()->to('/module/settings')->with('success', lang('Settings.saved'));
    }

    protected function canManageUsers(): bool
    {
        $tenantId = (int) (service('tenantContext')->getTenant()['id'] ?? 0);
        $userId   = (int) session('user_id');

        if (session('is_platform_admin')) {
            return true;
        }

        $row = model(\App\Models\TenantMembershipModel::class)
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->first();

        return $row !== null && in_array($row['role'], ['owner', 'admin'], true);
    }
}
