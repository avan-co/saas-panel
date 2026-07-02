<?php

namespace App\Controllers\Concerns;

trait HasTenantModule
{
    protected function requireModule(string $code): ?array
    {
        $tenantContext = service('tenantContext');

        if (! $tenantContext->hasModule($code)) {
            return null;
        }

        try {
            \Config\Services::migrations()->latest();
        } catch (\Throwable) {
            return null;
        }

        return $tenantContext->getTenant();
    }

    protected function moduleBreadcrumbs(string $moduleLabel, ?string $moduleUrl = null, ?string $pageLabel = null): array
    {
        $crumbs = [
            ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
        ];

        if ($pageLabel === null) {
            $crumbs[] = ['label' => $moduleLabel];

            return $crumbs;
        }

        $crumbs[] = ['label' => $moduleLabel, 'url' => $moduleUrl];
        $crumbs[] = ['label' => $pageLabel];

        return $crumbs;
    }
}
