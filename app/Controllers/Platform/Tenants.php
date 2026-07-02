<?php

namespace App\Controllers\Platform;

use App\Controllers\BaseController;
use App\Models\ModuleModel;
use App\Models\TenantModel;
use App\Models\UserModel;

class Tenants extends BaseController
{
    public function index()
    {
        $tenantModel = model(TenantModel::class);
        $userModel   = model(UserModel::class);
        $moduleModel = model(ModuleModel::class);

        $tenants = $tenantModel->orderBy('created_at', 'DESC')->findAll();

        foreach ($tenants as &$tenant) {
            $owner = $userModel->find($tenant['owner_id']);
            $tenant['owner_name']  = $owner['name'] ?? '-';
            $tenant['owner_email'] = $owner['email'] ?? '-';
            $tenant['modules']     = $moduleModel->getForTenant((int) $tenant['id']);
        }

        return $this->render('platform/tenants/index', [
            'title'   => lang('Platform.title'),
            'tenants' => $tenants,
        ]);
    }
}
