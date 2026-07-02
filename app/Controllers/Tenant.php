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

        return redirect()->to('/dashboard');
    }
}
