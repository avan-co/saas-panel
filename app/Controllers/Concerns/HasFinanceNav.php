<?php

namespace App\Controllers\Concerns;

trait HasFinanceNav
{
    protected function financeNavItems(): array
    {
        return config('ModuleMenus')->finance;
    }

    protected function financeBreadcrumbs(?string $pageLabel = null): array
    {
        return $this->moduleBreadcrumbs(
            lang('Finance.title'),
            $pageLabel ? site_url('module/finance') : null,
            $pageLabel,
        );
    }
}
