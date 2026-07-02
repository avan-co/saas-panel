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

    public array $settings = [
        ['key' => 'general',       'route' => 'module/settings',              'label' => 'Settings.title'],
        ['key' => 'users',         'route' => 'module/settings/users',        'label' => 'Settings.users'],
        ['key' => 'integrations',  'route' => 'module/settings/integrations', 'label' => 'Settings.integrations'],
        ['key' => 'audit',         'route' => 'module/settings/audit',        'label' => 'Settings.audit_log'],
        ['key' => 'period_locks',  'route' => 'module/settings/period-locks', 'label' => 'Settings.period_locks'],
    ];
}
