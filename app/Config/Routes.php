<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Web Installer
$routes->get('install', 'Install::index');
$routes->get('install/database', 'Install::database');
$routes->post('install/database', 'Install::saveDatabase');
$routes->get('install/setup', 'Install::setup');
$routes->post('install/setup', 'Install::runSetup');
$routes->get('install/process', 'Install::process');
$routes->post('install/execute', 'Install::execute');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');

$routes->get('locale/(:segment)', 'Preferences::locale/$1');
$routes->get('theme/(:segment)', 'Preferences::theme/$1');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index', ['filter' => 'tenant']);
    $routes->get('tenant/switch/(:num)', 'Tenant::switch/$1');

    $routes->group('module/finance', ['filter' => 'tenant'], static function ($routes) {
        $routes->get('/', 'Finance::index');
        $routes->get('transactions', 'Finance::transactions');
        $routes->get('transactions/new', 'Finance::createTransaction');
        $routes->post('transactions/store', 'Finance::storeTransaction');
        $routes->get('transactions/(:num)/edit', 'Finance::editTransaction/$1');
        $routes->post('transactions/(:num)/update', 'Finance::updateTransaction/$1');
        $routes->post('transactions/(:num)/delete', 'Finance::deleteTransaction/$1');
        $routes->get('accounts', 'FinanceAccounts::index');
        $routes->get('accounts/new', 'FinanceAccounts::create');
        $routes->post('accounts/store', 'FinanceAccounts::store');
        $routes->get('accounts/(:num)/edit', 'FinanceAccounts::edit/$1');
        $routes->post('accounts/(:num)/update', 'FinanceAccounts::update/$1');
        $routes->post('accounts/(:num)/delete', 'FinanceAccounts::delete/$1');
        $routes->get('categories', 'FinanceCategories::index');
        $routes->get('categories/new', 'FinanceCategories::create');
        $routes->post('categories/store', 'FinanceCategories::store');
        $routes->get('categories/(:num)/edit', 'FinanceCategories::edit/$1');
        $routes->post('categories/(:num)/update', 'FinanceCategories::update/$1');
        $routes->post('categories/(:num)/delete', 'FinanceCategories::delete/$1');
        $routes->get('budgets', 'FinanceBudgets::index');
        $routes->post('budgets/store', 'FinanceBudgets::store');
        $routes->post('budgets/(:num)/delete', 'FinanceBudgets::delete/$1');
        $routes->get('reminders', 'FinanceReminders::index');
        $routes->post('reminders/store', 'FinanceReminders::store');
        $routes->post('reminders/(:num)/paid', 'FinanceReminders::markPaid/$1');
        $routes->get('reports', 'FinanceReports::index');
    });

    $routes->get('notifications', 'Notifications::index');
    $routes->get('notifications/dropdown', 'Notifications::dropdown');
    $routes->post('notifications/(:num)/read', 'Notifications::markRead/$1');

    $routes->get('module/payroll', 'Payroll::index', ['filter' => 'tenant']);
    $routes->get('module/payroll/employees/new', 'Payroll::createEmployee', ['filter' => 'tenant']);
    $routes->post('module/payroll/employees/store', 'Payroll::storeEmployee', ['filter' => 'tenant']);
    $routes->get('module/payroll/employees/(:num)/edit', 'Payroll::editEmployee/$1', ['filter' => 'tenant']);
    $routes->post('module/payroll/employees/(:num)/update', 'Payroll::updateEmployee/$1', ['filter' => 'tenant']);
    $routes->post('module/payroll/employees/(:num)/delete', 'Payroll::deleteEmployee/$1', ['filter' => 'tenant']);

    $routes->get('module/insurance', 'Insurance::index', ['filter' => 'tenant']);
    $routes->get('module/insurance/new', 'Insurance::create', ['filter' => 'tenant']);
    $routes->post('module/insurance/store', 'Insurance::store', ['filter' => 'tenant']);
    $routes->get('module/insurance/(:num)/edit', 'Insurance::edit/$1', ['filter' => 'tenant']);
    $routes->post('module/insurance/(:num)/update', 'Insurance::update/$1', ['filter' => 'tenant']);
    $routes->post('module/insurance/(:num)/delete', 'Insurance::delete/$1', ['filter' => 'tenant']);

    $routes->get('module/tax', 'Tax::index', ['filter' => 'tenant']);
    $routes->get('module/tax/new', 'Tax::create', ['filter' => 'tenant']);
    $routes->post('module/tax/store', 'Tax::store', ['filter' => 'tenant']);
    $routes->get('module/tax/(:num)/edit', 'Tax::edit/$1', ['filter' => 'tenant']);
    $routes->post('module/tax/(:num)/update', 'Tax::update/$1', ['filter' => 'tenant']);
    $routes->post('module/tax/(:num)/delete', 'Tax::delete/$1', ['filter' => 'tenant']);

    $routes->get('module/projects', 'Projects::index', ['filter' => 'tenant']);
    $routes->get('module/projects/new', 'Projects::create', ['filter' => 'tenant']);
    $routes->post('module/projects/store', 'Projects::store', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/edit', 'Projects::edit/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/update', 'Projects::update/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/delete', 'Projects::delete/$1', ['filter' => 'tenant']);

    $routes->get('module/settings', 'Settings::index', ['filter' => 'tenant']);
    $routes->post('module/settings', 'Settings::update', ['filter' => 'tenant']);
    $routes->get('module/settings/users', 'TenantUsers::index', ['filter' => 'tenant']);
    $routes->post('module/settings/users/store', 'TenantUsers::store', ['filter' => 'tenant']);

    $routes->get('module/(:segment)', 'ModulePage::show/$1', ['filter' => 'tenant']);

    $routes->group('platform', ['filter' => 'platformadmin'], static function ($routes) {
        $routes->get('tenants', 'Platform\Tenants::index');
    });
});
