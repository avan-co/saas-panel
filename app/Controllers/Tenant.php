<?php

namespace App\Controllers;

class Tenant extends BaseController
{
    public function switch(int $tenantId)
    {
        $userId = (int) session('user_id');

        if (! service('tenantContext')->load($tenantId, $userId)) {
            return redirect()->back()->with('error', lang('App.no_tenant_access'));
        }

        $tenant = service('tenantContext')->getTenant();

        return redirect()->to('/dashboard')->with('success', lang('App.tenant_switched', ['name' => $tenant['name'] ?? '']));
    }
}
