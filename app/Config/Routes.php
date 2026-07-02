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
        $routes->get('reports/export/transactions', 'FinanceReports::exportTransactions');
        $routes->get('reports/export/journal', 'FinanceReports::exportJournal');
        $routes->get('approvals', 'FinanceApprovals::index');
        $routes->post('approvals/(:num)/approve', 'FinanceApprovals::approve/$1');
        $routes->post('approvals/(:num)/reject', 'FinanceApprovals::reject/$1');
        $routes->get('forecast', 'FinanceForecast::index');
        $routes->post('forecast/scenario', 'FinanceForecast::scenario');
        $routes->get('contacts', 'FinanceContacts::index');
        $routes->get('contacts/new', 'FinanceContacts::create');
        $routes->post('contacts/store', 'FinanceContacts::store');
        $routes->get('contacts/(:num)', 'FinanceContacts::show/$1');
        $routes->get('contacts/(:num)/edit', 'FinanceContacts::edit/$1');
        $routes->post('contacts/(:num)/update', 'FinanceContacts::update/$1');
        $routes->post('contacts/(:num)/delete', 'FinanceContacts::delete/$1');
        $routes->get('invoices', 'FinanceInvoices::index');
        $routes->get('invoices/new', 'FinanceInvoices::create');
        $routes->post('invoices/store', 'FinanceInvoices::store');
        $routes->get('invoices/(:num)/edit', 'FinanceInvoices::edit/$1');
        $routes->post('invoices/(:num)/update', 'FinanceInvoices::update/$1');
        $routes->post('invoices/(:num)/delete', 'FinanceInvoices::delete/$1');
        $routes->post('invoices/(:num)/pay', 'FinanceInvoices::pay/$1');
        $routes->post('invoices/(:num)/modian', 'FinanceInvoices::submitModian/$1');
        $routes->get('invoices/files/(:num)/download', 'FinanceInvoices::download/$1');
        $routes->get('checks', 'FinanceChecks::index');
        $routes->get('checks/new', 'FinanceChecks::create');
        $routes->post('checks/store', 'FinanceChecks::store');
        $routes->get('checks/(:num)/edit', 'FinanceChecks::edit/$1');
        $routes->post('checks/(:num)/update', 'FinanceChecks::update/$1');
        $routes->post('checks/(:num)/delete', 'FinanceChecks::delete/$1');
        $routes->get('loans', 'FinanceLoans::index');
        $routes->get('loans/new', 'FinanceLoans::create');
        $routes->post('loans/store', 'FinanceLoans::store');
        $routes->get('loans/(:num)/edit', 'FinanceLoans::edit/$1');
        $routes->post('loans/(:num)/update', 'FinanceLoans::update/$1');
        $routes->post('loans/(:num)/installment', 'FinanceLoans::payInstallment/$1');
        $routes->post('loans/(:num)/delete', 'FinanceLoans::delete/$1');
        $routes->get('assets', 'FinanceAssets::index');
        $routes->get('assets/new', 'FinanceAssets::create');
        $routes->post('assets/store', 'FinanceAssets::store');
        $routes->get('assets/(:num)/edit', 'FinanceAssets::edit/$1');
        $routes->post('assets/(:num)/update', 'FinanceAssets::update/$1');
        $routes->post('assets/(:num)/delete', 'FinanceAssets::delete/$1');
    });

    $routes->get('search', 'Search::index', ['filter' => 'tenant']);

    $routes->get('notifications', 'Notifications::index');
    $routes->get('notifications/dropdown', 'Notifications::dropdown');
    $routes->post('notifications/(:num)/read', 'Notifications::markRead/$1');

    $routes->get('module/payroll', 'Payroll::index', ['filter' => 'tenant']);
    $routes->get('module/payroll/employees/new', 'Payroll::createEmployee', ['filter' => 'tenant']);
    $routes->post('module/payroll/employees/store', 'Payroll::storeEmployee', ['filter' => 'tenant']);
    $routes->get('module/payroll/employees/(:num)/edit', 'Payroll::editEmployee/$1', ['filter' => 'tenant']);
    $routes->post('module/payroll/employees/(:num)/update', 'Payroll::updateEmployee/$1', ['filter' => 'tenant']);
    $routes->post('module/payroll/employees/(:num)/delete', 'Payroll::deleteEmployee/$1', ['filter' => 'tenant']);
    $routes->get('module/payroll/runs', 'PayrollRuns::index', ['filter' => 'tenant']);
    $routes->get('module/payroll/runs/new', 'PayrollRuns::create', ['filter' => 'tenant']);
    $routes->post('module/payroll/runs/store', 'PayrollRuns::store', ['filter' => 'tenant']);
    $routes->get('module/payroll/runs/(:num)', 'PayrollRuns::show/$1', ['filter' => 'tenant']);
    $routes->post('module/payroll/runs/(:num)/approve', 'PayrollRuns::approve/$1', ['filter' => 'tenant']);
    $routes->get('module/payroll/runs/(:num)/dsk', 'PayrollRuns::exportDsk/$1', ['filter' => 'tenant']);

    $routes->get('module/insurance', 'Insurance::index', ['filter' => 'tenant']);
    $routes->get('module/insurance/new', 'Insurance::create', ['filter' => 'tenant']);
    $routes->post('module/insurance/store', 'Insurance::store', ['filter' => 'tenant']);
    $routes->get('module/insurance/(:num)/edit', 'Insurance::edit/$1', ['filter' => 'tenant']);
    $routes->post('module/insurance/(:num)/update', 'Insurance::update/$1', ['filter' => 'tenant']);
    $routes->post('module/insurance/(:num)/delete', 'Insurance::delete/$1', ['filter' => 'tenant']);
    $routes->post('module/insurance/(:num)/pay', 'Insurance::markPaid/$1', ['filter' => 'tenant']);

    $routes->get('module/tax', 'Tax::index', ['filter' => 'tenant']);
    $routes->get('module/tax/new', 'Tax::create', ['filter' => 'tenant']);
    $routes->post('module/tax/store', 'Tax::store', ['filter' => 'tenant']);
    $routes->get('module/tax/(:num)/edit', 'Tax::edit/$1', ['filter' => 'tenant']);
    $routes->post('module/tax/(:num)/update', 'Tax::update/$1', ['filter' => 'tenant']);
    $routes->post('module/tax/(:num)/delete', 'Tax::delete/$1', ['filter' => 'tenant']);
    $routes->post('module/tax/(:num)/pay', 'Tax::markPaid/$1', ['filter' => 'tenant']);

    $routes->get('module/projects/workload', 'Projects::workload', ['filter' => 'tenant']);
    $routes->get('module/projects', 'Projects::index', ['filter' => 'tenant']);
    $routes->get('module/projects/new', 'Projects::create', ['filter' => 'tenant']);
    $routes->post('module/projects/store', 'Projects::store', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)', 'Projects::show/$1', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/edit', 'Projects::edit/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/update', 'Projects::update/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/delete', 'Projects::delete/$1', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/gantt', 'ProjectGantt::index/$1', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/calendar', 'ProjectCalendar::index/$1', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/files', 'ProjectFiles::index/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/files/store', 'ProjectFiles::store/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/files/(:num)/approve', 'ProjectFiles::approve/$1/$2', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/files/(:num)/download', 'ProjectFiles::download/$1/$2', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/risks', 'ProjectRisks::index/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/risks/store', 'ProjectRisks::storeRisk/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/issues/store', 'ProjectRisks::storeIssue/$1', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/reports', 'ProjectReports::index/$1', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/wiki', 'ProjectWiki::index/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/wiki/pages', 'ProjectWiki::storePage/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/wiki/decisions', 'ProjectWiki::storeDecision/$1', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/tasks/(:num)', 'ProjectTasks::show/$1/$2', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/tasks/(:num)/update', 'ProjectTasks::update/$1/$2', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/tasks/(:num)/comments', 'ProjectTasks::addComment/$1/$2', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/tasks/(:num)/checklist', 'ProjectTasks::addChecklist/$1/$2', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/tasks/(:num)/checklist/(:num)/toggle', 'ProjectTasks::toggleChecklist/$1/$2/$3', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/tasks', 'ProjectTasks::index/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/tasks/store', 'ProjectTasks::store/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/tasks/(:num)/status', 'ProjectTasks::updateStatus/$1/$2', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/tasks/(:num)/delete', 'ProjectTasks::delete/$1/$2', ['filter' => 'tenant']);
    $routes->get('module/projects/(:num)/timesheets', 'ProjectTimesheets::index/$1', ['filter' => 'tenant']);
    $routes->post('module/projects/(:num)/timesheets/store', 'ProjectTimesheets::store/$1', ['filter' => 'tenant']);

    $routes->get('module/settings', 'Settings::index', ['filter' => 'tenant']);
    $routes->post('module/settings', 'Settings::update', ['filter' => 'tenant']);
    $routes->get('module/settings/users', 'TenantUsers::index', ['filter' => 'tenant']);
    $routes->post('module/settings/users/store', 'TenantUsers::store', ['filter' => 'tenant']);
    $routes->post('module/settings/users/(:num)/update', 'TenantUsers::update/$1', ['filter' => 'tenant']);
    $routes->get('module/settings/teams', 'SettingsTeams::index', ['filter' => 'tenant']);
    $routes->post('module/settings/teams/store', 'SettingsTeams::store', ['filter' => 'tenant']);
    $routes->post('module/settings/teams/(:num)/update', 'SettingsTeams::update/$1', ['filter' => 'tenant']);
    $routes->post('module/settings/teams/(:num)/delete', 'SettingsTeams::delete/$1', ['filter' => 'tenant']);

    $routes->get('module/persons', 'Persons::index', ['filter' => 'tenant']);
    $routes->get('module/persons/new', 'Persons::create', ['filter' => 'tenant']);
    $routes->post('module/persons/store', 'Persons::store', ['filter' => 'tenant']);
    $routes->get('module/persons/(:num)/edit', 'Persons::edit/$1', ['filter' => 'tenant']);
    $routes->post('module/persons/(:num)/update', 'Persons::update/$1', ['filter' => 'tenant']);
    $routes->post('module/persons/(:num)/delete', 'Persons::delete/$1', ['filter' => 'tenant']);
    $routes->get('module/settings/modules', 'SettingsModules::index', ['filter' => 'tenant']);
    $routes->get('module/settings/api', 'SettingsApi::index', ['filter' => 'tenant']);
    $routes->post('module/settings/api/keys', 'SettingsApi::storeApiKey', ['filter' => 'tenant']);
    $routes->post('module/settings/api/keys/(:num)/delete', 'SettingsApi::deleteApiKey/$1', ['filter' => 'tenant']);
    $routes->post('module/settings/api/webhooks', 'SettingsApi::storeWebhook', ['filter' => 'tenant']);
    $routes->post('module/settings/api/webhooks/(:num)/delete', 'SettingsApi::deleteWebhook/$1', ['filter' => 'tenant']);
    $routes->get('module/settings/audit', 'SettingsAudit::index', ['filter' => 'tenant']);
    $routes->get('module/settings/period-locks', 'SettingsPeriodLock::index', ['filter' => 'tenant']);
    $routes->post('module/settings/period-locks', 'SettingsPeriodLock::lock', ['filter' => 'tenant']);

    $routes->get('module/(:segment)', 'ModulePage::show/$1', ['filter' => 'tenant']);

    $routes->group('platform', ['filter' => 'platformadmin'], static function ($routes) {
        $routes->get('tenants', 'Platform\Tenants::index');
        $routes->get('tenants/new', 'Platform\Tenants::create');
        $routes->post('tenants/store', 'Platform\Tenants::store');
        $routes->get('tenants/(:num)', 'Platform\Tenants::show/$1');
        $routes->get('tenants/(:num)/edit', 'Platform\Tenants::edit/$1');
        $routes->post('tenants/(:num)/update', 'Platform\Tenants::update/$1');
        $routes->post('tenants/(:num)/suspend', 'Platform\Tenants::suspend/$1');
        $routes->post('tenants/(:num)/delete', 'Platform\Tenants::delete/$1');
        $routes->get('users', 'Platform\Users::index');
        $routes->post('users/(:num)/toggle-admin', 'Platform\Users::toggleAdmin/$1');
        $routes->get('system', 'Platform\System::index');
    });
});

$routes->group('api/v1', ['filter' => 'apikey'], static function ($routes) {
    $routes->get('transactions', 'Api\V1\Transactions::index');
    $routes->post('transactions', 'Api\V1\Transactions::store');
    $routes->get('transactions/(:num)', 'Api\V1\Transactions::show/$1');
});
