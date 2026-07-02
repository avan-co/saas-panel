<?php

namespace App\Controllers;

use App\Controllers\Concerns\ChecksPermission;
use App\Controllers\Concerns\HasSettingsNav;
use App\Models\PersonModel;
use App\Models\PersonRoleModel;

class Persons extends BaseController
{
    use HasSettingsNav;
    use ChecksPermission;

    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null) {
            return redirect()->to('/dashboard');
        }

        if (! $this->requirePermission('settings.view') && ! $this->canManageSettings()) {
            return $this->permissionDeniedRedirect();
        }

        $tenantId = (int) $tenant['id'];
        $persons  = model(PersonModel::class)->getForTenant($tenantId);
        $roleModel = model(PersonRoleModel::class);

        foreach ($persons as &$person) {
            $person['roles'] = $roleModel->rolesForPerson((int) $person['id']);
        }

        return $this->render('persons/index', [
            'title'          => lang('Persons.title'),
            'moduleNav'      => 'persons',
            'moduleNavItems' => $this->settingsNavItems(),
            'persons'        => $persons,
            'breadcrumbs'    => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Persons.title')],
            ],
        ]);
    }

    public function create()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        return $this->render('persons/form', [
            'title'       => lang('Persons.new'),
            'person'      => null,
            'breadcrumbs' => [
                ['label' => lang('Persons.title'), 'url' => site_url('module/persons')],
                ['label' => lang('Persons.new')],
            ],
        ]);
    }

    public function store()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        if (! $this->validate($this->personRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantId = (int) $tenant['id'];
        $personId = model(PersonModel::class)->insert($this->personPayload($tenantId));
        $this->syncRoles((int) $personId);

        return redirect()->to('/module/persons')->with('success', lang('Persons.saved'));
    }

    public function edit(int $id)
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        $person = model(PersonModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($person === null) {
            return redirect()->to('/module/persons')->with('error', lang('App.not_found'));
        }

        $person['roles'] = model(PersonRoleModel::class)->rolesForPerson($id);

        return $this->render('persons/form', [
            'title'       => lang('Persons.edit'),
            'person'      => $person,
            'breadcrumbs' => [
                ['label' => lang('Persons.title'), 'url' => site_url('module/persons')],
                ['label' => lang('Persons.edit')],
            ],
        ]);
    }

    public function update(int $id)
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        $personModel = model(PersonModel::class);
        $person      = $personModel->findForTenant($id, (int) $tenant['id']);

        if ($person === null) {
            return redirect()->to('/module/persons')->with('error', lang('App.not_found'));
        }

        if (! $this->validate($this->personRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $personModel->update($id, $this->personPayload((int) $tenant['id']));
        $this->syncRoles($id);

        return redirect()->to('/module/persons')->with('success', lang('Persons.updated'));
    }

    public function delete(int $id)
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canManageSettings()) {
            return $this->settingsDeniedRedirect();
        }

        $person = model(PersonModel::class)->findForTenant($id, (int) $tenant['id']);

        if ($person === null) {
            return redirect()->to('/module/persons')->with('error', lang('App.not_found'));
        }

        model(PersonRoleModel::class)->where('person_id', $id)->delete();
        model(PersonModel::class)->delete($id);

        return redirect()->to('/module/persons')->with('success', lang('App.deleted'));
    }

    protected function personRules(): array
    {
        return [
            'name'  => 'required|max_length[160]',
            'email' => 'permit_empty|valid_email',
            'phone' => 'permit_empty|max_length[30]',
        ];
    }

    protected function personPayload(int $tenantId): array
    {
        return [
            'tenant_id'   => $tenantId,
            'name'        => (string) $this->request->getPost('name'),
            'national_id' => (string) $this->request->getPost('national_id'),
            'phone'       => (string) $this->request->getPost('phone'),
            'email'       => (string) $this->request->getPost('email'),
            'address'     => (string) $this->request->getPost('address'),
            'note'        => (string) $this->request->getPost('note'),
        ];
    }

    protected function syncRoles(int $personId): void
    {
        $roles = $this->request->getPost('roles') ?: [];
        $roleModel = model(PersonRoleModel::class);
        $roleModel->where('person_id', $personId)->delete();

        foreach ((array) $roles as $role) {
            if ($role !== '') {
                $roleModel->insert(['person_id' => $personId, 'role' => (string) $role]);
            }
        }
    }
}
