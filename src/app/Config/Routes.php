<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'DashboardController::index', ['filter' => 'auth']);
// --------------------------------------------------------------------
// กำหนด URL สำหรับระบบ Login
// --------------------------------------------------------------------
// ใน Routes.php
$routes->get('/login', 'AuthController::index');
$routes->get('logout', 'AuthController::logout');
$routes->post('/login', 'AuthController::attemptLogin');
$routes->get('/reset-password-for-mana', 'AuthController::resetPassword');
$routes->get('/products', 'ProductController::index');

// --- Public Routes for Authentication ---
$routes->get('/register', 'AuthController::register');
$routes->post('/register', 'AuthController::attemptRegister');
// (คุณอาจจะมี /login, /logout ที่นี่ด้วย)
// --- LINE Login Routes ---
$routes->get('login/line', 'AuthController::lineLogin');
$routes->get('login/callback', 'AuthController::lineCallback');
// --- AJAX Routes ---
// สร้าง Group สำหรับ AJAX โดยเฉพาะเพื่อให้จัดการง่าย
$routes->group('ajax', function ($routes) {
    $routes->post('get-provinces', 'AdminController::getProvinces');
    $routes->post('get-amphures', 'AuthController::getAmphures');
    $routes->post('get-hospitals', 'AuthController::getHospitals'); // <-- เพิ่ม Route นี้
    $routes->post('get-villages', 'AuthController::getVillages'); // <-- Route นี้จะทำงานกับ Logic ใหม่

    // คุณสามารถย้าย get-tambons, get-villages มาไว้ที่นี่ได้ในอนาคต
});
// app/Config/Routes.php
$routes->group('patients', ['filter' => 'auth'], function ($routes) {
    // CRUD Routes
    $routes->get('/', 'PatientController::index');
    $routes->get('fetch', 'PatientController::fetchPatients');
    $routes->post('store', 'PatientController::store');
    $routes->get('fetch-one/(:num)', 'PatientController::fetchSinglePatient/$1');
    $routes->post('update', 'PatientController::update');
    $routes->post('delete/(:num)', 'PatientController::delete/$1');

    // Feature Routes
    $routes->post('update-risk-level', 'PatientController::updateRiskLevel');

    // AJAX Routes for Dropdowns
    $routes->post('get-amphures', 'PatientController::getAmphures');
    $routes->post('get-tambons', 'PatientController::getTambons');
    $routes->post('get-villages', 'PatientController::getVillages');
    $routes->get('search-person', 'PatientController::searchPerson'); // <-- เปลี่ยนจาก search-external
    $routes->get('check-existing', 'PatientController::checkExistingPatient');
});


$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    // หน้านี้จะเข้าได้เฉพาะ Admin อำเภอ
    $routes->get('user-approval', 'AdminController::userApproval');

    // AJAX routes for the approval page
    $routes->get('users/pending', 'AdminController::fetchPendingUsers');
    $routes->post('users/approve', 'AdminController::approveUser'); // <-- Route ใหม่
    $routes->post('users/reject/(:num)', 'AdminController::rejectUser/$1');
    $routes->get('users/get-details/(:num)', 'AdminController::getUserDetails/$1');
    // --- Routes ใหม่สำหรับหน้าจัดการผู้ใช้งาน ---
    $routes->get('manage-users', 'AdminController::manageUsers');
    $routes->get('users/fetch', 'AdminController::fetchUsers');
    $routes->get('users/fetch-one/(:num)', 'AdminController::fetchUserForEdit/$1');
    $routes->post('users/update', 'AdminController::updateUser');
    $routes->get('settings', 'SettingsController::index');
    $routes->post('settings', 'SettingsController::save');
    $routes->get('settings/province', 'SettingsController::province');
    $routes->post('settings/province/save', 'SettingsController::saveProvince');
});

$routes->group('dashboard', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'DashboardController::index');
    $routes->get('data', 'DashboardController::getChartData'); // Endpoint for AJAX
    $routes->post('get-hospitals', 'DashboardController::getHospitals');
});

$routes->group('followup', ['filter' => 'auth'], function ($routes) {
    $routes->get('patient/(:num)', 'FollowUpController::patient/$1');
    $routes->post('store', 'FollowUpController::store');
});

$routes->group('import', ['filter' => 'auth'], function ($routes) {
    $routes->get('patients', 'ImportController::index');
    $routes->post('patients/upload', 'ImportController::upload');
    $routes->get('download-template', 'ImportController::downloadTemplate');
});


$routes->group('reports', ['filter' => 'auth'], function ($routes) {
    $routes->get('patient-summary', 'ReportController::patientSummary');
    $routes->post('get-data', 'ReportController::getReportData');
    $routes->post('get-hospitals', 'ReportController::getHospitalsInAmphur');
    $routes->get('visit-summary', 'ReportController::visitSummary');
    $routes->post('ajax-get-visit-summary', 'ReportController::ajaxGetVisitSummary');
});

$routes->group('notifications', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'NotificationController::index');
    $routes->get('fetch-unread', 'NotificationController::fetchUnread');
    $routes->get('fetch-all', 'NotificationController::fetchAll');
    $routes->get('mark-as-read/(:num)', 'NotificationController::markAsRead/$1');
});