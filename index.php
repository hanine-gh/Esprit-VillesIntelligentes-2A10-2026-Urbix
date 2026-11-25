<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Africa/Tunis');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/helpers.php';

spl_autoload_register(function (string $class) {
    $path = __DIR__ . '/app/' . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$routes = require __DIR__ . '/app/routes.php';
$page   = $_GET['page'] ?? 'home';

if (!isset($routes[$page])) {
    http_response_code(404);
    exit('Page introuvable');
}

[$class, $method] = $routes[$page];
(new $class)->$method();
