<?php
// ── Bootstrap ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/../Config/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

// ── Session ───────────────────────────────────────────────────────────────
use App\Security\CsrfGuard;
use App\Security\SessionManager;

$sessionManager = new SessionManager(new CsrfGuard());
$sessionManager->startSecure();

// ── Routing ───────────────────────────────────────────────────────────────
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

$publicRoutes = ['/login', '/signup'];

if (!in_array($uri, $publicRoutes) && !$sessionManager->checkTimeout()) {
    header('Location: /login');
    exit;
}

// Load routes
$router = require '../Routes/web.php';
$router->dispatch($uri, $method);
