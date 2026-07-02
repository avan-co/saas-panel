<?php

namespace App\Filters;

use App\Libraries\TenantContext;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = session('user_id');

        if ($userId === null) {
            return redirect()->to('/login');
        }

        /** @var TenantContext $tenantContext */
        $tenantContext = service('tenantContext');

        if (! $tenantContext->loadFromSession((int) $userId)) {
            return redirect()->to('/login')->with('error', lang('App.no_tenant_access'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
