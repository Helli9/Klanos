<?php
// Depends on SESSION_TIMEOUT defined in bootstrap.php
// Always require bootstrap.php before this file.

function session_start_secure(): void {
    if (session_status() !== PHP_SESSION_NONE) {
        return; // Session already active — do nothing
    }

    // ── Detect HTTPS ───────────────────────────────────────────────────────
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
             || ($_SERVER['SERVER_PORT'] ?? 80) == 443;

    session_set_cookie_params([
        'lifetime' => 0,          // Expires when browser closes
        'path'     => '/',
        'domain'   => '',         // Defaults to current host; set explicitly in .env if needed
        'secure'   => $isSecure,      // HTTPS only
        'httponly' => true,       // No JS access (XSS protection)
        'samesite' => 'Strict',   // No cross-site sending (CSRF protection)
    ]);

    // Harden session INI settings before starting
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_trans_sid', 0);
    ini_set('session.cache_limiter', 'nocache');

    session_start();
}


function check_session_timeout(): bool {
    if (!defined('SESSION_TIMEOUT')) {
        throw new RuntimeException('SESSION_TIMEOUT is not defined. Load bootstrap.php first.');
    }

    // First visit — initialise the timer
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return true;
    }

    if ((time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        // Expire the session cleanly
        $_SESSION = [];
        session_destroy();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        return false;
    }

    $_SESSION['last_activity'] = time(); // Refresh timer
    return true;
}