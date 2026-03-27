<?php
// Router for PHP built-in server: php -S localhost:8000 router.php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$routes = [
    '/admin' => __DIR__ . '/admin.php',
];

if (isset($routes[$uri])) {
    require $routes[$uri];
    return true;
}

// Serve static files as-is
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Fall through to index.php for unmatched routes
require __DIR__ . '/index.php';
