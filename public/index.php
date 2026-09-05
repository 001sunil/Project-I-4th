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

// Error and exception handlers
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
    $errorMap = [
        E_WARNING => 'WARNING',
        E_NOTICE  => 'NOTICE',
        E_ERROR   => 'ERROR',
    ];
    $level = $errorMap[$errno] ?? 'ERROR';

    // Use @ to suppress further errors from Logger itself
    @\Core\Logger::error("[{$level}] {$errstr}", [
        'file' => $errfile,
        'line' => $errline,
    ]);

    if ($errno === E_ERROR || $errno === E_USER_ERROR) {
        http_response_code(500);
        require __DIR__ . '/../app/Views/errors/500.php';
        exit;
    }

    return true;
});

set_exception_handler(function (\Throwable $e) {
    @\Core\Logger::error('Uncaught exception: ' . $e->getMessage(), [
        'file'  => $e->getFile(),
        'line'  => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    http_response_code(500);
    require __DIR__ . '/../app/Views/errors/500.php';
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        @\Core\Logger::error('Fatal error: ' . $error['message'], [
            'file' => $error['file'],
            'line' => $error['line'],
        ]);

        http_response_code(500);
        require __DIR__ . '/../app/Views/errors/500.php';
        exit;
    }
});

// Session with secure cookie settings
if (session_status() === PHP_SESSION_NONE) {
    $isProduction = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly'  => true,
        'secure'   => $isProduction,
        'samesite' => 'Strict',
    ]);
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
