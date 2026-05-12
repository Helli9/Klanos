<?php
// ── Bootstrap ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/session_check.php';
require_once __DIR__ . '/../vendor/autoload.php';

// ── Session ───────────────────────────────────────────────────────────────
session_start_secure();

// ── Routing ───────────────────────────────────────────────────────────────
// 1. Cleaning the Address
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// Public routes that should NEVER redirect-loop
$publicRoutes = ['/', '/login', '/signup'];

// Only check timeout on protected pages
if (!in_array($uri, $publicRoutes) && !check_session_timeout()) {
    header('Location: /login');
    exit;
}

// Load routes
$router = require '../routes/web.php';
$router->dispatch($uri, $method);