<?php
// ============================================================
// ENTRY POINT: public/index.php
// All requests are routed through this file.
// ============================================================

// PSR-4 Autoloader
spl_autoload_register(function (string $class) {
    $prefixes = [
        'App\\'         => __DIR__ . '/../app/',
        'Core\\'        => __DIR__ . '/../core/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Base URL for all links and redirects (e.g., /medistock/public)
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

// Load Routes (creates $router variable with all routes registered)
require __DIR__ . '/../routes/web.php';

// Strip the base path so the Router sees paths like /, /login, etc.
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$uri = substr($uri, strlen($basePath)) ?: '/';

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
