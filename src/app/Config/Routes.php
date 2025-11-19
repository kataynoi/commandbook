<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// หน้าแรกหลังล็อกอิน -> Commands dashboard
$routes->get('/', 'Commands::index', ['filter' => 'auth']);
$routes->get('/privacy-policy', 'Home::privacy');
// --------------------------------------------------------------------
// กำหนด URL สำหรับระบบ Login / Register
// --------------------------------------------------------------------
$routes->get('/login', 'AuthController::index');
$routes->post('/login', 'AuthController::attemptLogin');
$routes->get('/logout', 'AuthController::logout');

$routes->get('/register', 'AuthController::register');
$routes->post('/register', 'AuthController::attemptRegister');

// LINE Login
$routes->get('login/line', 'AuthController::lineLogin');
$routes->get('login/callback', 'AuthController::lineCallback');

// --------------------------------------------------------------------
// AJAX Routes
// --------------------------------------------------------------------
$routes->group('ajax', function ($routes) {
    $routes->post('get-provinces', 'AdminController::getProvinces');
    $routes->post('get-amphures', 'AuthController::getAmphures');
    $routes->get('get-hospitals', 'AuthController::getHospitals');
    $routes->post('get-hospitals', 'AuthController::getHospitals');
    $routes->post('get-villages', 'AuthController::getVillages');
});

// --------------------------------------------------------------------
// Admin (ต้องการการยืนยันตัวตน)
// --------------------------------------------------------------------
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    $routes->get('user-approval', 'AdminController::userApproval');
    $routes->get('get-hospitals', 'AdminController::getHospitals');

    $routes->get('users/pending', 'AdminController::fetchPendingUsers');
    $routes->post('users/approve', 'AdminController::approveUser');
    $routes->post('users/reject/(:num)', 'AdminController::rejectUser/$1');
    $routes->get('users/get-details/(:num)', 'AdminController::getUserDetails/$1');

    $routes->get('manage-users', 'AdminController::manageUsers');
    $routes->get('users/fetch', 'AdminController::fetchUsers');
    $routes->get('users/fetch-one/(:num)', 'AdminController::fetchUserForEdit/$1');
    $routes->post('users/update', 'AdminController::updateUser');

    $routes->get('settings', 'SettingsController::index');
    $routes->post('settings', 'SettingsController::save');
    $routes->get('settings/province', 'SettingsController::province');
    $routes->post('settings/province/save', 'SettingsController::saveProvince');
});

// --------------------------------------------------------------------
// Dashboard (AJAX endpoints for charts)
// --------------------------------------------------------------------
$routes->group('dashboard', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'DashboardController::index');
    $routes->get('data', 'DashboardController::getChartData');
    $routes->post('get-hospitals', 'DashboardController::getHospitals');
});

// --------------------------------------------------------------------
// Reports
// --------------------------------------------------------------------
// Commands (document upload / access)
// --------------------------------------------------------------------
$routes->get('commands', 'Commands::index');
$routes->get('commands/create', 'Commands::create');
$routes->post('commands/save', 'Commands::save');
$routes->get('commands/success', 'Commands::success');

// AJAX / API for commands DataTable and actions
$routes->get('commands/fetch', 'Commands::fetch');           // DataTable source
$routes->get('commands/get/(:num)', 'Commands::get/$1');    // get details
$routes->post('commands/delete/(:num)', 'Commands::delete/$1'); // delete (POST)
$routes->get('commands/qr/(:segment)', 'Commands::qr/$1');

$routes->get('access/(:segment)', 'Access::index/$1');