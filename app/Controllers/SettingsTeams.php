<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasSettingsNav;
use App\Models\TenantMembershipModel;
use App\Models\TenantTeamMemberModel;
use App\Models\TenantTeamModel;

class SettingsTeams extends BaseController
{
    use HasSettingsNav;

    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return redirect()->to('/dashboard');
        }

        if (! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];

        return $this->render('settings/teams/index', [
            'title'          => lang('Settings.teams'),
            'moduleNav'      => 'teams',
            'moduleNavItems' => $this->settingsNavItems(),
            'teams'          => model(TenantTeamModel::class)->withMembers($tenantId),
            'members'        => model(TenantMembershipModel::class)->getForTenant($tenantId),
            'breadcrumbs'    => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Settings.title'), 'url' => site_url('module/settings')],
                ['label' => lang('Settings.teams')],
            ],
        ]);
    }

    public function store()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        $rules = ['name' => 'required|max_length[120]'];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantId = (int) $tenant['id'];
        $teamId   = model(TenantTeamModel::class)->insert([
            'tenant_id'      => $tenantId,
            'name'           => (string) $this->request->getPost('name'),
            'description'    => (string) $this->request->getPost('description'),
            'leader_user_id' => $this->request->getPost('leader_user_id') ? (int) $this->request->getPost('leader_user_id') : null,
        ]);

        $userIds = $this->request->getPost('member_user_id') ?: [];
        model(TenantTeamMemberModel::class)->syncMembers((int) $teamId, (array) $userIds);

        return redirect()->to('/module/settings/teams')->with('success', lang('Settings.team_created'));
    }

    public function update(int $id)
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $team     = model(TenantTeamModel::class)->findForTenant($id, $tenantId);

        if ($team === null) {
            return redirect()->to('/module/settings/teams')->with('error', lang('App.not_found'));
        }

        model(TenantTeamModel::class)->update($id, [
            'name'           => (string) $this->request->getPost('name'),
            'description'    => (string) $this->request->getPost('description'),
            'leader_user_id' => $this->request->getPost('leader_user_id') ? (int) $this->request->getPost('leader_user_id') : null,
        ]);

        $userIds = $this->request->getPost('member_user_id') ?: [];
        model(TenantTeamMemberModel::class)->syncMembers($id, (array) $userIds);

        return redirect()->to('/module/settings/teams')->with('success', lang('Settings.team_updated'));
    }

    public function delete(int $id)
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $team     = model(TenantTeamModel::class)->findForTenant($id, $tenantId);

        if ($team === null) {
            return redirect()->to('/module/settings/teams')->with('error', lang('App.not_found'));
        }

        model(TenantTeamMemberModel::class)->where('team_id', $id)->delete();
        model(TenantTeamModel::class)->delete($id);

        return redirect()->to('/module/settings/teams')->with('success', lang('App.deleted'));
    }
}
