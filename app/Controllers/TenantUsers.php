<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use App\Models\TenantMembershipModel;
use App\Models\UserModel;

class TenantUsers extends BaseController
{
    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return redirect()->to('/dashboard');
        }

        if (! $this->canManageUsers()) {
            return redirect()->to('/module/settings')->with('error', lang('Settings.no_permission'));
        }

        $membershipModel = model(TenantMembershipModel::class);

        return $this->render('settings/users/index', [
            'title'          => lang('Settings.users'),
            'moduleNav'      => 'users',
            'moduleNavItems' => config('ModuleMenus')->settings,
            'members'        => $membershipModel->getForTenant((int) $tenant['id']),
            'orgTree'        => $membershipModel->orgTree((int) $tenant['id']),
            'breadcrumbs'    => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Settings.title'), 'url' => site_url('module/settings')],
                ['label' => lang('Settings.users')],
            ],
        ]);
    }

    public function update(int $id)
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageUsers()) {
            return redirect()->to('/module/settings')->with('error', lang('Settings.no_permission'));
        }

        $model      = model(TenantMembershipModel::class);
        $membership = $model->where('id', $id)->where('tenant_id', (int) $tenant['id'])->first();

        if ($membership === null) {
            return redirect()->to('/module/settings/users')->with('error', lang('App.not_found'));
        }

        $rules = [
            'role' => 'required|in_list[admin,accountant,hr,viewer,manager]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $perms = $this->request->getPost('permissions');
        $permsJson = is_array($perms) ? json_encode(array_values($perms)) : null;

        $model->update($id, [
            'role'        => (string) $this->request->getPost('role'),
            'department'  => (string) $this->request->getPost('department'),
            'manager_id'  => $this->request->getPost('manager_id') ? (int) $this->request->getPost('manager_id') : null,
            'permissions' => $permsJson,
        ]);

        return redirect()->to('/module/settings/users')->with('success', lang('Settings.user_updated'));
    }

    public function store()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageUsers()) {
            return redirect()->to('/module/settings')->with('error', lang('Settings.no_permission'));
        }

        $rules = [
            'name'     => 'required|min_length[2]|max_length[120]',
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[admin,accountant,hr,viewer,manager]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = model(UserModel::class);
        $email     = (string) $this->request->getPost('email');
        $user      = $userModel->findByEmail($email);

        if ($user === null) {
            $userId = $userModel->insert([
                'name'              => (string) $this->request->getPost('name'),
                'email'             => $email,
                'password'          => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
                'locale'            => 'fa',
                'theme'             => 'system',
                'is_platform_admin' => 0,
                'status'            => 'active',
            ]);
        } else {
            $userId = (int) $user['id'];
        }

        $membershipModel = model(TenantMembershipModel::class);

        if ($membershipModel->userBelongsToTenant($userId, (int) $tenant['id'])) {
            return redirect()->back()->with('error', lang('Settings.user_exists'));
        }

        $perms = $this->request->getPost('permissions');
        $permsJson = is_array($perms) ? json_encode(array_values($perms)) : null;

        $membershipModel->insert([
            'tenant_id'   => (int) $tenant['id'],
            'user_id'     => $userId,
            'role'        => (string) $this->request->getPost('role'),
            'department'  => (string) $this->request->getPost('department'),
            'manager_id'  => $this->request->getPost('manager_id') ? (int) $this->request->getPost('manager_id') : null,
            'permissions' => $permsJson,
        ]);

        model(NotificationModel::class)->notifyUser(
            $userId,
            lang('Settings.user_invited_title', ['tenant' => $tenant['name']]),
            lang('Settings.user_invited_body'),
            site_url('login'),
            (int) $tenant['id'],
        );

        return redirect()->to('/module/settings/users')->with('success', lang('Settings.user_added'));
    }

    protected function canManageUsers(): bool
    {
        $tenantId = (int) (service('tenantContext')->getTenant()['id'] ?? 0);
        $userId   = (int) session('user_id');

        if (session('is_platform_admin')) {
            return true;
        }

        $row = model(TenantMembershipModel::class)
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->first();

        return $row !== null && in_array($row['role'], ['owner', 'admin'], true);
    }
}
