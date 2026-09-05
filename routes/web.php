<?php
// ============================================================
// ROUTES: web.php
// Maps URLs to Controller@Method handlers.
// ============================================================

$router = new \Core\Router();

// ---- Auth Routes (Guest only) ----
$router->get('/login',         'AuthController@showLogin')->middleware('guest');
$router->post('/login',        'AuthController@login')->middleware('guest');
$router->post('/logout',       'AuthController@logout')->middleware('auth');

// ---- Dashboard ----
$router->get('/',              'DashboardController@index')->middleware('auth');
$router->get('/dashboard',     'DashboardController@index')->middleware('auth');

// ---- Medicines ----
$router->get('/medicines',              'MedicineController@index')->middleware('auth');
$router->get('/medicines/create',       'MedicineController@create')->middleware('auth');
$router->post('/medicines/store',       'MedicineController@store')->middleware('auth');
$router->get('/medicines/{id}',         'MedicineController@show')->middleware('auth');
$router->get('/medicines/{id}/edit',    'MedicineController@edit')->middleware('auth');
$router->post('/medicines/{id}/update', 'MedicineController@update')->middleware('auth');
$router->post('/medicines/{id}/delete', 'MedicineController@destroy')->middleware('auth', 'admin');

// ---- Sales ----
$router->get('/sales',              'SaleController@index')->middleware('auth');
$router->get('/sales/create',       'SaleController@create')->middleware('auth');
$router->post('/sales/store',       'SaleController@store')->middleware('auth');
$router->get('/sales/export',       'SaleController@export')->middleware('auth');

// ---- Stock Adjustments ----
$router->get('/stock-adjustments',           'StockAdjustmentController@index')->middleware('auth');
$router->get('/stock-adjustments/create',    'StockAdjustmentController@create')->middleware('auth');
$router->post('/stock-adjustments/store',    'StockAdjustmentController@store')->middleware('auth');

// ---- Reports ----
$router->get('/reports/low-stock',       'ReportController@lowStock')->middleware('auth');
$router->get('/reports/expiry',          'ReportController@expiryAlerts')->middleware('auth');
$router->get('/reports/prescriptions',   'ReportController@prescriptionAudit')->middleware('auth');

// ---- Users (Admin only) ----
$router->get('/users',              'UserController@index')->middleware('auth', 'admin');
$router->get('/users/create',       'UserController@create')->middleware('auth', 'admin');
$router->post('/users/store',       'UserController@store')->middleware('auth', 'admin');
$router->get('/users/{id}/edit',    'UserController@edit')->middleware('auth', 'admin');
$router->post('/users/{id}/update', 'UserController@update')->middleware('auth', 'admin');
$router->post('/users/{id}/delete', 'UserController@destroy')->middleware('auth', 'admin');

// ---- Settings (Admin only) ----
$router->get('/settings',           'SettingsController@index')->middleware('auth', 'admin');
$router->post('/settings/update',   'SettingsController@update')->middleware('auth', 'admin');

// ---- Avatars ----
$router->get('/avatars/{filename}', 'AvatarController@show');
