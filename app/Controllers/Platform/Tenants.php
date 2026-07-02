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

    public function edit(int $id)
    {
        $tenant = model(TenantModel::class)->find($id);

        if ($tenant === null) {
            return redirect()->to('/platform/tenants')->with('error', lang('App.not_found'));
        }

        $allModules     = model(ModuleModel::class)->getActive();
        $enabledModules = model(ModuleModel::class)->getForTenant($id);
        $enabledIds     = array_column($enabledModules, 'id');

        return $this->render('platform/tenants/form', [
            'title'       => lang('Platform.edit_tenant'),
            'tenant'      => $tenant,
            'allModules'  => $allModules,
            'enabledIds'  => $enabledIds,
        ]);
    }

    public function update(int $id)
    {
        $tenantModel = model(TenantModel::class);
        $tenant      = $tenantModel->find($id);

        if ($tenant === null) {
            return redirect()->to('/platform/tenants')->with('error', lang('App.not_found'));
        }

        $rules = [
            'name'   => 'required|max_length[191]',
            'status' => 'required|in_list[active,suspended,trial]',
            'plan'   => 'required|max_length[40]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantModel->update($id, [
            'name'   => (string) $this->request->getPost('name'),
            'status' => (string) $this->request->getPost('status'),
            'plan'   => (string) $this->request->getPost('plan'),
        ]);

        $moduleIds = $this->request->getPost('modules') ?? [];
        $db        = \Config\Database::connect();
        $db->table('tenant_modules')->where('tenant_id', $id)->delete();

        foreach ($moduleIds as $moduleId) {
            $db->table('tenant_modules')->insert([
                'tenant_id'  => $id,
                'module_id'  => (int) $moduleId,
                'enabled'    => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/platform/tenants')->with('success', lang('Platform.tenant_updated'));
    }

    public function suspend(int $id)
    {
        model(TenantModel::class)->update($id, ['status' => 'suspended']);

        return redirect()->to('/platform/tenants')->with('success', lang('Platform.tenant_suspended'));
    }
}
