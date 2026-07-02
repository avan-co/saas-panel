<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ModuleMenus extends BaseConfig
{
    public array $finance = [
        ['key' => 'overview',      'route' => 'module/finance',              'label' => 'Finance.overview'],
        ['key' => 'transactions',  'route' => 'module/finance/transactions', 'label' => 'Finance.transactions'],
        ['key' => 'accounts',      'route' => 'module/finance/accounts',     'label' => 'Finance.accounts'],
        ['key' => 'categories',    'route' => 'module/finance/categories',   'label' => 'Finance.categories'],
        ['key' => 'contacts',      'route' => 'module/finance/contacts',     'label' => 'Finance.contacts'],
        ['key' => 'invoices',      'route' => 'module/finance/invoices',     'label' => 'Finance.invoices'],
        ['key' => 'checks',        'route' => 'module/finance/checks',       'label' => 'Finance.checks'],
        ['key' => 'loans',         'route' => 'module/finance/loans',        'label' => 'Finance.loans'],
        ['key' => 'assets',        'route' => 'module/finance/assets',       'label' => 'Finance.assets'],
        ['key' => 'approvals',     'route' => 'module/finance/approvals',    'label' => 'Finance.approvals'],
        ['key' => 'budgets',       'route' => 'module/finance/budgets',      'label' => 'Finance.budgets'],
        ['key' => 'reminders',     'route' => 'module/finance/reminders',    'label' => 'Finance.reminders'],
        ['key' => 'forecast',      'route' => 'module/finance/forecast',     'label' => 'Finance.forecast'],
        ['key' => 'reports',       'route' => 'module/finance/reports',      'label' => 'Finance.reports'],
    ];

    /** تنظیمات پنل کسب‌وکار — فقط ادمین/مالک tenant */
    public array $settings = [
        ['key' => 'general',      'route' => 'module/settings',              'label' => 'Settings.business_profile'],
        ['key' => 'users',        'route' => 'module/settings/users',        'label' => 'Settings.users'],
        ['key' => 'teams',        'route' => 'module/settings/teams',        'label' => 'Settings.teams'],
        ['key' => 'modules',      'route' => 'module/settings/modules',      'label' => 'Settings.module_harmony'],
        ['key' => 'audit',        'route' => 'module/settings/audit',        'label' => 'Settings.audit_log'],
        ['key' => 'period_locks', 'route' => 'module/settings/period-locks', 'label' => 'Settings.period_locks'],
        ['key' => 'api',          'route' => 'module/settings/api',          'label' => 'Settings.api_access'],
    ];

    /** مدیریت سامانه — فقط سوپرادمین پلتفرم */
    public array $platform = [
        ['key' => 'tenants', 'route' => 'platform/tenants', 'label' => 'Platform.tenants'],
        ['key' => 'users',   'route' => 'platform/users',   'label' => 'Platform.users'],
        ['key' => 'system',  'route' => 'platform/system',  'label' => 'Platform.system_settings'],
    ];
}
