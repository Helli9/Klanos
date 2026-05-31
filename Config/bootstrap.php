<?php
require_once __DIR__ . '/../App/Core/ErrorHandler.php';
use App\Core\ErrorHandler;

ErrorHandler::register();

// ── 1. Load .env ──────────────────────────────────────────────────────────
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    //2. Reading and Filtering
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '='))         continue;

        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}

// ── 2. Session Cookie Security Configuration ──────────────────────────────
// This MUST run before session_start() is called anywhere in your application.
session_set_cookie_params([
    'lifetime' => 0,                      // Cookie expires when the browser closes
    'path'     => '/',
    'domain'   => '',                     // Current domain
    'secure'   => true,                   // Ensures cookie is only sent over HTTPS
    'httponly' => true,                   // Prevents JavaScript access (mitigates XSS cookie theft)
    'samesite' => 'Lax'                   // Mitigates CSRF by restricting cross-site cookie transmission
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── 3. Constants ──────────────────────────────────────────────────────────
defined('BASE_URL')        || define('BASE_URL',        $_ENV['APP_BASE_URL'] ?? '/');
defined('SESSION_TIMEOUT') || define('SESSION_TIMEOUT', 1800); // 30 minutes

// ── 4. Helpers ────────────────────────────────────────────────────────────
if (!function_exists('e')) {
    function e(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}