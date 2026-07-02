<?php

namespace App\Controllers\Concerns;

trait HasSettingsNav
{
    use ManagesTenantSettings;

    protected function settingsNavItems(): array
    {
        $items  = config('ModuleMenus')->settings;
        $result = [];

        foreach ($items as $item) {
            if ($this->settingsNavAllowed($item['key'])) {
                $result[] = $item;
            }
        }

        return $result;
    }

    protected function settingsNavAllowed(string $key): bool
    {
        if ($key === 'general' || $key === 'modules') {
            return $this->canViewSettings();
        }

        return $this->canManageSettings();
    }
}
