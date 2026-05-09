<?php
// ── Bootstrap ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/session_check.php';
require_once __DIR__ . '/../vendor/autoload.php';

// ── Session ───────────────────────────────────────────────────────────────
session_start_secure();

// ── Routing ───────────────────────────────────────────────────────────────
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// Public routes that should NEVER redirect-loop
$publicRoutes = ['/', '/login', '/signup'];

// Only check timeout on protected pages
if (!in_array($uri, $publicRoutes) && !check_session_timeout()) {
    header('Location: /login');
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Load routes
$routes = require __DIR__ . '/../routes/web.php';

// ── Dispatch ──────────────────────────────────────────────────────────────
if (isset($routes[$method][$uri])) {
    [$controller, $action] = $routes[$method][$uri];

    $controllerInstance = new $controller();
    $controllerInstance->$action();

} else {
    http_response_code(404);
    echo "404 - Page not found";
}