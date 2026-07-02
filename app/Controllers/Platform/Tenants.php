<?php

namespace App\Controllers\Platform;

use App\Controllers\BaseController;
use App\Controllers\Concerns\HasPlatformNav;
use App\Models\ModuleModel;
use App\Models\TenantMembershipModel;
use App\Models\TenantModel;
use App\Models\UserModel;

class Tenants extends BaseController
{
    use HasPlatformNav;

    public function index()
    {
        $tenantModel = model(TenantModel::class);
        $userModel   = model(UserModel::class);
        $moduleModel = model(ModuleModel::class);
        $membershipModel = model(TenantMembershipModel::class);

        $tenants = $tenantModel->getActiveList();
        $stats   = ['active' => 0, 'trial' => 0, 'suspended' => 0];

        foreach ($tenants as &$tenant) {
            $owner = $userModel->find($tenant['owner_id']);
            $tenant['owner_name']  = $owner['name'] ?? '-';
            $tenant['owner_email'] = $owner['email'] ?? '-';
            $tenant['owner_last_login'] = $owner['last_login_at'] ?? null;
            $tenant['modules']     = $moduleModel->getForTenant((int) $tenant['id']);
            $tenant['member_count'] = $membershipModel->where('tenant_id', (int) $tenant['id'])->countAllResults();
            $stats[$tenant['status']] = ($stats[$tenant['status']] ?? 0) + 1;
        }

        return $this->render('platform/tenants/index', [
            'title'          => lang('Platform.title'),
            'moduleNav'      => 'tenants',
            'moduleNavItems' => $this->platformNavItems(),
            'tenants'        => $tenants,
            'stats'          => $stats,
            'breadcrumbs'    => [
                ['label' => lang('Platform.title')],
            ],
        ]);
    }

    public function create()
    {
        return $this->render('platform/tenants/form', [
            'title'          => lang('Platform.create_tenant'),
            'moduleNav'      => 'tenants',
            'moduleNavItems' => $this->platformNavItems(),
            'tenant'         => null,
            'allModules'     => model(ModuleModel::class)->getActive(),
            'enabledIds'     => [],
            'breadcrumbs'    => [
                ['label' => lang('Platform.title'), 'url' => site_url('platform/tenants')],
                ['label' => lang('Platform.create_tenant')],
            ],
        ]);
    }

    public function store()
    {
        $rules = [
            'name'        => 'required|max_length[191]',
            'slug'        => 'required|alpha_dash|max_length[120]',
            'owner_name'  => 'required|max_length[120]',
            'owner_email' => 'required|valid_email',
            'status'      => 'required|in_list[active,suspended,trial]',
            'plan'        => 'required|max_length[40]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $slug = (string) $this->request->getPost('slug');

        if (model(TenantModel::class)->slugExists($slug)) {
            return redirect()->back()->withInput()->with('error', lang('Platform.slug_exists'));
        }

        $userModel = model(UserModel::class);
        $email     = (string) $this->request->getPost('owner_email');
        $owner     = $userModel->findByEmail($email);

        if ($owner === null) {
            $password = (string) ($this->request->getPost('owner_password') ?: 'password');
            $ownerId  = $userModel->insert([
                'name'              => (string) $this->request->getPost('owner_name'),
                'email'             => $email,
                'password'          => password_hash($password, PASSWORD_DEFAULT),
                'locale'            => 'fa',
                'theme'             => 'system',
                'is_platform_admin' => 0,
                'status'            => 'active',
            ]);
        } else {
            $ownerId = (int) $owner['id'];
        }

        $now = date('Y-m-d H:i:s');
        $tenantModel = model(TenantModel::class);
        $tenantId = $tenantModel->insert([
            'name'                   => (string) $this->request->getPost('name'),
            'slug'                   => $slug,
            'owner_id'               => $ownerId,
            'status'                 => (string) $this->request->getPost('status'),
            'plan'                   => (string) $this->request->getPost('plan'),
            'subscription_starts_at' => $this->request->getPost('subscription_starts_at') ?: $now,
            'subscription_ends_at'   => $this->request->getPost('subscription_ends_at') ?: date('Y-m-d H:i:s', strtotime('+1 year')),
            'timezone'               => 'Asia/Tehran',
            'currency'               => 'IRR',
            'fiscal_year_start'      => 1,
        ]);

        model(TenantMembershipModel::class)->insert([
            'tenant_id' => $tenantId,
            'user_id'   => $ownerId,
            'role'      => 'owner',
        ]);

        $this->syncModules((int) $tenantId);

        return redirect()->to('/platform/tenants')->with('success', lang('Platform.tenant_created'));
    }

    public function show(int $id)
    {
        $tenant = model(TenantModel::class)->find($id);

        if ($tenant === null || $tenant['deleted_at'] !== null) {
            return redirect()->to('/platform/tenants')->with('error', lang('App.not_found'));
        }

        $userModel = model(UserModel::class);
        $owner     = $userModel->find($tenant['owner_id']);
        $members   = model(TenantMembershipModel::class)->getForTenant($id);

        foreach ($members as &$member) {
            $user = $userModel->find($member['user_id']);
            $member['last_login_at'] = $user['last_login_at'] ?? null;
            $member['last_login_ip'] = $user['last_login_ip'] ?? null;
        }

        return $this->render('platform/tenants/show', [
            'title'          => $tenant['name'],
            'moduleNav'      => 'tenants',
            'moduleNavItems' => $this->platformNavItems(),
            'tenant'         => $tenant,
            'owner'          => $owner,
            'members'        => $members,
            'modules'        => model(ModuleModel::class)->getForTenant($id),
            'breadcrumbs'    => [
                ['label' => lang('Platform.title'), 'url' => site_url('platform/tenants')],
                ['label' => $tenant['name']],
            ],
        ]);
    }

    public function edit(int $id)
    {
        $tenant = model(TenantModel::class)->find($id);

        if ($tenant === null || $tenant['deleted_at'] !== null) {
            return redirect()->to('/platform/tenants')->with('error', lang('App.not_found'));
        }

        $allModules     = model(ModuleModel::class)->getActive();
        $enabledModules = model(ModuleModel::class)->getForTenant($id);
        $enabledIds     = array_column($enabledModules, 'id');

        return $this->render('platform/tenants/form', [
            'title'          => lang('Platform.edit_tenant'),
            'moduleNav'      => 'tenants',
            'moduleNavItems' => $this->platformNavItems(),
            'tenant'         => $tenant,
            'allModules'     => $allModules,
            'enabledIds'     => $enabledIds,
            'breadcrumbs'    => [
                ['label' => lang('Platform.title'), 'url' => site_url('platform/tenants')],
                ['label' => lang('Platform.edit_tenant')],
            ],
        ]);
    }

    public function update(int $id)
    {
        $tenantModel = model(TenantModel::class);
        $tenant      = $tenantModel->find($id);

        if ($tenant === null || $tenant['deleted_at'] !== null) {
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
            'name'                   => (string) $this->request->getPost('name'),
            'status'                 => (string) $this->request->getPost('status'),
            'plan'                   => (string) $this->request->getPost('plan'),
            'subscription_starts_at' => $this->request->getPost('subscription_starts_at') ?: $tenant['subscription_starts_at'],
            'subscription_ends_at'   => $this->request->getPost('subscription_ends_at') ?: $tenant['subscription_ends_at'],
        ]);

        $this->syncModules($id);

        return redirect()->to('/platform/tenants/' . $id)->with('success', lang('Platform.tenant_updated'));
    }

    public function suspend(int $id)
    {
        model(TenantModel::class)->update($id, ['status' => 'suspended']);

        return redirect()->to('/platform/tenants')->with('success', lang('Platform.tenant_suspended'));
    }

    public function delete(int $id)
    {
        $tenantModel = model(TenantModel::class);
        $tenant      = $tenantModel->find($id);

        if ($tenant === null || $tenant['deleted_at'] !== null) {
            return redirect()->to('/platform/tenants')->with('error', lang('App.not_found'));
        }

        $tenantModel->update($id, [
            'status'     => 'suspended',
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/platform/tenants')->with('success', lang('Platform.tenant_deleted'));
    }

    protected function syncModules(int $tenantId): void
    {
        $moduleIds = $this->request->getPost('modules') ?? [];
        $db        = \Config\Database::connect();
        $db->table('tenant_modules')->where('tenant_id', $tenantId)->delete();

        foreach ($moduleIds as $moduleId) {
            $db->table('tenant_modules')->insert([
                'tenant_id'  => $tenantId,
                'module_id'  => (int) $moduleId,
                'enabled'    => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
