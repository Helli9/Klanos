<?php
require_once __DIR__ . '/../App/Core/ErrorHandler.php';
use App\Core\ErrorHandler;
ErrorHandler::register();


// ── 1. Load .env ──────────────────────────────────────────────────────────
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '='))         continue;

        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}

// ── 2. Constants ──────────────────────────────────────────────────────────
defined('BASE_URL')        || define('BASE_URL',        '/myApp/public');
defined('SESSION_TIMEOUT') || define('SESSION_TIMEOUT', 1800); // 30 minutes

// ── 3. Helpers ────────────────────────────────────────────────────────────
if (!function_exists('e')) {
    function e(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}