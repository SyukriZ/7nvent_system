<?php
// =============================================================
// 7NVENT - Front Controller / Router
// public/index.php
// =============================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/Auth.php';

// Get the route from URL
$request = $_SERVER['REQUEST_URI'];
$basePath = parse_url(APP_URL, PHP_URL_PATH);
$route = str_replace($basePath, '', $request);
$route = strtok($route, '?'); // Remove query string
$route = rtrim($route, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// CORS for the mobile API — needed for the Flutter *web* build (Safari on
// iPhone, Chrome, etc.) since browsers enforce CORS on cross-origin fetch
// calls; the native Android app (using package:http, not a browser) never
// hits this. '*' is fine here because these endpoints are already
// JWT-guarded — an allowed origin can't do anything a valid bearer token
// wouldn't already permit.
if (str_starts_with($route, '/api/')) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    if ($method === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// Route definitions
$routes = [
    'GET' => [
        '/'                 => 'LandingController@index',
        '/login'            => 'AuthController@showLogin',
        '/logout'           => 'AuthController@logout',
        '/dashboard'        => 'DashboardController@index',
        '/inventory'        => 'InventoryController@index',
        '/inventory/create' => 'InventoryController@create',
        '/inventory/edit'   => 'InventoryController@edit',
        '/purchase-orders'  => 'PurchaseOrderController@index',
        '/purchase-orders/create' => 'PurchaseOrderController@create',
        '/purchase-orders/view'   => 'PurchaseOrderController@view',
        '/alerts'           => 'AlertController@index',
        '/suppliers'        => 'SupplierController@index',
        '/suppliers/create' => 'SupplierController@create',
        '/locations'        => 'LocationController@index',
        '/reports'          => 'ReportController@index',
        '/reports/generate' => 'ReportController@generate',
        '/users'            => 'UserController@index',
        '/users/create'     => 'UserController@create',
        '/users/edit'       => 'UserController@edit',
        '/settings'         => 'SettingsController@index',
        '/analytics'        => 'AnalyticsController@index',
        '/qr-scanner'       => 'QRController@scanner',
        '/inventory/scan-log' => 'QRController@scanLog',

        // Public (no login) product page — this is what a printed QR label
        // actually opens when scanned with a phone's plain camera app.
        '/product/view'     => 'ProductController@show',

        // --- Mobile JSON API (JWT-guarded, used by the Flutter app) ---
        '/api/public/stats'     => 'AuthApiController@publicStats', // no auth — pre-login screen
        '/api/auth/me'          => 'AuthApiController@me',
        '/api/inventory'        => 'InventoryApiController@index',
        '/api/inventory/detail' => 'InventoryApiController@detail',
        '/api/inventory/lookup' => 'InventoryApiController@lookup',
        '/api/inventory/meta'   => 'InventoryApiController@meta',
        '/api/dashboard'        => 'DashboardApiController@index',
        '/api/locations'            => 'LocationApiController@index',
        '/api/suppliers'            => 'SupplierApiController@index',
        '/api/purchase-orders'      => 'PurchaseOrderApiController@index',
        '/api/purchase-orders/meta' => 'PurchaseOrderApiController@meta',
        '/api/purchase-orders/view' => 'PurchaseOrderApiController@view',
        '/api/alerts'               => 'AlertApiController@index',
        '/api/users'                => 'UserApiController@index',
        '/api/users/meta'           => 'UserApiController@meta',
        '/api/users/detail'         => 'UserApiController@detail',
        '/api/settings'             => 'SettingsApiController@index',
        '/api/analytics'            => 'AnalyticsApiController@index',
        '/api/reports'              => 'ReportApiController@index',
        '/api/reports/generate'     => 'ReportApiController@generate',
    ],
    'POST' => [
        '/login'            => 'AuthController@login',
        '/inventory/store'  => 'InventoryController@store',
        '/inventory/update' => 'InventoryController@update',
        '/inventory/delete' => 'InventoryController@delete',
        '/inventory/quick-add' => 'InventoryController@quickAddAjax',
        '/inventory/upload-image' => 'InventoryController@uploadImage',
        '/purchase-orders/store'  => 'PurchaseOrderController@store',
        '/purchase-orders/update' => 'PurchaseOrderController@update',
        '/alerts/resolve'   => 'AlertController@resolve',
        '/suppliers/store'  => 'SupplierController@store',
        '/locations/update' => 'LocationController@update',
        '/users/store'      => 'UserController@store',
        '/users/update'     => 'UserController@update',
        '/settings/update'        => 'SettingsController@update',
        '/settings/manual-backup' => 'SettingsController@manualBackup',
        '/inventory/qr-update' => 'QRController@qrUpdate',

        // --- Mobile JSON API (JWT-guarded, used by the Flutter app) ---
        '/api/auth/login'         => 'AuthApiController@login',
        '/api/auth/logout'        => 'AuthApiController@logout',
        '/api/inventory/store'    => 'InventoryApiController@store',
        '/api/inventory/update'   => 'InventoryApiController@update',
        '/api/inventory/delete'   => 'InventoryApiController@delete',
        '/api/inventory/quick-add' => 'InventoryApiController@quickAdd',
        '/api/locations/update'       => 'LocationApiController@update',
        '/api/suppliers/store'        => 'SupplierApiController@store',
        '/api/purchase-orders/store'  => 'PurchaseOrderApiController@store',
        '/api/purchase-orders/update' => 'PurchaseOrderApiController@update',
        '/api/alerts/resolve'         => 'AlertApiController@resolve',
        '/api/users/store'            => 'UserApiController@store',
        '/api/users/update'           => 'UserApiController@update',
        '/api/settings/update'        => 'SettingsApiController@update',
        '/api/settings/manual-backup' => 'SettingsApiController@manualBackup',
    ],
];

// Match route
$handler = $routes[$method][$route] ?? null;

if ($handler) {
    [$controllerName, $action] = explode('@', $handler);
    $controllerFile = __DIR__ . '/../app/Http/Controllers/' . $controllerName . '.php';

    // Api*Controller classes live one level deeper, in Controllers/Api/, but
    // aren't PHP-namespaced (same bare class name new $controllerName()
    // expects below) — so only the file lookup needs the extra subfolder.
    if (!file_exists($controllerFile)) {
        $apiControllerFile = __DIR__ . '/../app/Http/Controllers/Api/' . $controllerName . '.php';
        if (file_exists($apiControllerFile)) {
            $controllerFile = $apiControllerFile;
        }
    }

    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controller = new $controllerName();
        $controller->$action();
    } else {
        http_response_code(500);
        echo "Controller not found: $controllerName";
    }
} else {
    // 404
    http_response_code(404);
    require_once __DIR__ . '/../resources/views/404.php';
}
