<?php

namespace App\Controllers\Concerns;

trait HasPlatformNav
{
    protected function platformNavItems(): array
    {
        return config('ModuleMenus')->platform;
    }
}
