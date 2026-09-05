<?php
// ============================================================
// ROUTES: web.php
// Maps URLs to Controller@Method handlers.
// ============================================================

$router = new \Core\Router();

// ---- Auth Routes (Guest only) ----
$router->get('/login',         'AuthController@showLogin');
$router->post('/login',        'AuthController@login');
$router->get('/logout',        'AuthController@logout');

// ---- Dashboard ----
$router->get('/',              'DashboardController@index');
$router->get('/dashboard',     'DashboardController@index');

// ---- Medicines ----
$router->get('/medicines',              'MedicineController@index');
$router->get('/medicines/create',       'MedicineController@create');
$router->post('/medicines/store',       'MedicineController@store');
$router->get('/medicines/{id}',         'MedicineController@show');
$router->get('/medicines/{id}/edit',    'MedicineController@edit');
$router->post('/medicines/{id}/update', 'MedicineController@update');
$router->post('/medicines/{id}/delete', 'MedicineController@destroy');

// ---- Sales ----
$router->get('/sales',              'SaleController@index');
$router->get('/sales/create',       'SaleController@create');
$router->post('/sales/store',       'SaleController@store');
$router->get('/sales/export',       'SaleController@export');

// ---- Stock Adjustments ----
$router->get('/stock-adjustments',           'StockAdjustmentController@index');
$router->get('/stock-adjustments/create',    'StockAdjustmentController@create');
$router->post('/stock-adjustments/store',    'StockAdjustmentController@store');

// ---- Reports ----
$router->get('/reports/low-stock',       'ReportController@lowStock');
$router->get('/reports/expiry',          'ReportController@expiryAlerts');
$router->get('/reports/prescriptions',   'ReportController@prescriptionAudit');

// ---- Users (Admin only) ----
$router->get('/users',              'UserController@index');
$router->get('/users/create',       'UserController@create');
$router->post('/users/store',       'UserController@store');
$router->get('/users/{id}/edit',    'UserController@edit');
$router->post('/users/{id}/update', 'UserController@update');
$router->post('/users/{id}/delete', 'UserController@destroy');

// ---- Settings (Admin only) ----
$router->get('/settings',           'SettingsController@index');
$router->post('/settings/update',   'SettingsController@update');

// ---- Avatars ----
$router->get('/avatars/{filename}', 'AvatarController@show');
