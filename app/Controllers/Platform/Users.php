<?php

namespace App\Controllers\Platform;

use App\Controllers\BaseController;
use App\Models\TenantMembershipModel;
use App\Models\UserModel;

class Users extends BaseController
{
    public function index()
    {
        $users = model(UserModel::class)->orderBy('created_at', 'DESC')->findAll();

        foreach ($users as &$user) {
            $user['tenants'] = model(TenantMembershipModel::class)
                ->select('tenants.name, tenant_memberships.role')
                ->join('tenants', 'tenants.id = tenant_memberships.tenant_id')
                ->where('tenant_memberships.user_id', $user['id'])
                ->findAll();
        }

        return $this->render('platform/users/index', [
            'title' => lang('Platform.users'),
            'users' => $users,
        ]);
    }

    public function toggleAdmin(int $id)
    {
        $userModel = model(UserModel::class);
        $user      = $userModel->find($id);

        if ($user === null) {
            return redirect()->to('/platform/users')->with('error', lang('App.not_found'));
        }

        $userModel->update($id, ['is_platform_admin' => $user['is_platform_admin'] ? 0 : 1]);

        return redirect()->to('/platform/users')->with('success', lang('Platform.user_updated'));
    }
}
