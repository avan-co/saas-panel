<?php

namespace App\Filters;

use App\Models\ApiKeyModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiKeyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');

        if (! str_starts_with($header, 'Bearer ')) {
            return service('response')->setStatusCode(401)->setJSON(['error' => 'Missing API key']);
        }

        $key = trim(substr($header, 7));

        if (! str_contains($key, '.')) {
            return service('response')->setStatusCode(401)->setJSON(['error' => 'Invalid API key format']);
        }

        [$prefix] = explode('.', $key, 2);
        $record   = model(ApiKeyModel::class)->findByPrefixAndHash($prefix, hash('sha256', $key));

        if ($record === null) {
            return service('response')->setStatusCode(401)->setJSON(['error' => 'Invalid API key']);
        }

        model(ApiKeyModel::class)->update($record['id'], ['last_used' => date('Y-m-d H:i:s')]);

        $tenant = model(\App\Models\TenantModel::class)->find($record['tenant_id']);

        if ($tenant === null || $tenant['status'] === 'suspended') {
            return service('response')->setStatusCode(403)->setJSON(['error' => 'Tenant suspended']);
        }

        service('tenantContext')->setTenant($tenant);
        $request->apiKey = $record;

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
