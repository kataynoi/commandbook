<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// หน้าแรกหลังล็อกอิน -> Dashboard
$routes->get('/', 'Dashboard::index', ['filter' => 'auth']);
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
// Activity Logs (เฉพาะ Admin)
// --------------------------------------------------------------------
$routes->group('activity-logs', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'ActivityLogController::index');
    $routes->post('fetch', 'ActivityLogController::fetch');
    $routes->post('delete/(:num)', 'ActivityLogController::delete/$1');
    $routes->post('cleanup', 'ActivityLogController::cleanup');
});

// --------------------------------------------------------------------
// Dashboard (AJAX endpoints for charts)
// --------------------------------------------------------------------
$routes->group('dashboard', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'DashboardController::index');
    $routes->get('data', 'DashboardController::getData');
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


/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
$routes->get('/dbtest', 'DbTestController::index');
// เพิ่ม Route สำหรับทดสอบการเชื่อมต่อฐานข้อมูล
