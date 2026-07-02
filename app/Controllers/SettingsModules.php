<?php

namespace App\Controllers;

use App\Controllers\Concerns\HasSettingsNav;

class SettingsModules extends BaseController
{
    use HasSettingsNav;

    public function index()
    {
        $tenant = service('tenantContext')->getTenant();

        if ($tenant === null || ! $this->canViewSettings()) {
            return $tenant === null ? redirect()->to('/dashboard') : $this->settingsDeniedRedirect();
        }

        $modules = service('tenantContext')->getModules();
        $links   = $this->moduleLinks($modules);

        return $this->render('settings/modules', [
            'title'          => lang('Settings.module_harmony'),
            'moduleNav'      => 'modules',
            'moduleNavItems' => $this->settingsNavItems(),
            'modules'        => $modules,
            'links'          => $links,
            'canManage'      => $this->canManageSettings(),
            'breadcrumbs'    => [
                ['label' => lang('App.menu.dashboard'), 'url' => site_url('dashboard')],
                ['label' => lang('Settings.title'), 'url' => site_url('module/settings')],
                ['label' => lang('Settings.module_harmony')],
            ],
        ]);
    }

  /**
     * @param list<array<string, mixed>> $modules
     * @return list<array{from: string, to: string, description: string, route: string}>
     */
    protected function moduleLinks(array $modules): array
    {
        $codes = array_column($modules, 'code');
        $links = [];

        $definitions = [
            ['finance', 'projects', 'Settings.link_finance_projects', 'module/finance'],
            ['finance', 'payroll', 'Settings.link_finance_payroll', 'module/payroll/runs'],
            ['finance', 'tax', 'Settings.link_finance_tax', 'module/finance/reminders'],
            ['finance', 'insurance', 'Settings.link_finance_insurance', 'module/finance/reminders'],
            ['payroll', 'finance', 'Settings.link_payroll_finance', 'module/payroll/runs'],
            ['projects', 'finance', 'Settings.link_projects_finance', 'module/projects'],
            ['tax', 'finance', 'Settings.link_tax_finance', 'module/tax'],
            ['insurance', 'finance', 'Settings.link_insurance_finance', 'module/insurance'],
        ];

        foreach ($definitions as [$from, $to, $label, $route]) {
            if (in_array($from, $codes, true) && in_array($to, $codes, true)) {
                $links[] = [
                    'from'        => lang('App.modules.' . $from),
                    'to'          => lang('App.modules.' . $to),
                    'description' => lang($label),
                    'route'       => $route,
                ];
            }
        }

        return $links;
    }
}
