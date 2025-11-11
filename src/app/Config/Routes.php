<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// หน้าแรกหลังล็อกอิน -> Commands dashboard
$routes->get('/', 'Commands::index', ['filter' => 'auth']);

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
// Patients (ต้องการการยืนยันตัวตน)
// --------------------------------------------------------------------
$routes->group('patients', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'PatientController::index');
    $routes->get('fetch', 'PatientController::fetchPatients');
    $routes->post('store', 'PatientController::store');
    $routes->get('fetch-one/(:num)', 'PatientController::fetchSinglePatient/$1');
    $routes->post('update', 'PatientController::update');
    $routes->post('delete/(:num)', 'PatientController::delete/$1');

    $routes->post('update-risk-level', 'PatientController::updateRiskLevel');

    $routes->post('get-amphures', 'PatientController::getAmphures');
    $routes->post('get-tambons', 'PatientController::getTambons');
    $routes->post('get-villages', 'PatientController::getVillages');
    $routes->get('search-person', 'PatientController::searchPerson');
    $routes->get('check-existing', 'PatientController::checkExistingPatient');
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
$routes->group('reports', ['filter' => 'auth'], function ($routes) {
    $routes->get('patient-summary', 'ReportController::patientSummary');
    $routes->post('get-data', 'ReportController::getReportData');
    $routes->post('get-hospitals', 'ReportController::getHospitalsInAmphur');
    $routes->get('visit-summary', 'ReportController::visitSummary');
    $routes->post('ajax-get-visit-summary', 'ReportController::ajaxGetVisitSummary');
});

// --------------------------------------------------------------------
// Commands (document upload / access)
// --------------------------------------------------------------------
$routes->get('commands', 'Commands::index');
$routes->get('commands/new', 'Commands::new');
$routes->post('commands/save', 'Commands::save');
$routes->get('commands/success', 'Commands::success');

// AJAX / API for commands DataTable and actions
$routes->get('commands/fetch', 'Commands::fetch');           // DataTable source
$routes->get('commands/get/(:num)', 'Commands::get/$1');    // get details
$routes->post('commands/delete/(:num)', 'Commands::delete/$1'); // delete (POST)
$routes->get('commands/qr/(:segment)', 'Commands::qr/$1');

$routes->get('access/(:segment)', 'Download::file/$1');
$routes->get('access/(:segment)', 'Access::index/$1');