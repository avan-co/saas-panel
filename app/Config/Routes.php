<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');

$routes->get('locale/(:segment)', 'Preferences::locale/$1');
$routes->get('theme/(:segment)', 'Preferences::theme/$1');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index', ['filter' => 'tenant']);
    $routes->get('tenant/switch/(:num)', 'Tenant::switch/$1');
    $routes->get('module/(:segment)', 'ModulePage::show/$1', ['filter' => 'tenant']);

    $routes->group('platform', ['filter' => 'platformadmin'], static function ($routes) {
        $routes->get('tenants', 'Platform\Tenants::index');
    });
});
