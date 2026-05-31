<?php

namespace App\Security;

use App\Security\CsrfGuard;

class SessionManager 
{
    private CsrfGuard $csrfGuard;


    public function __construct(CsrfGuard $csrfGuard) 
    {
        $this->csrfGuard = $csrfGuard;
    }

    function startSecure(): void 
    {
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
            'secure'   => $isSecure,  // HTTPS only
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

    public function checkTimeout(): bool
    {
        if (!defined('SESSION_TIMEOUT')) {
            throw new \RuntimeException('SESSION_TIMEOUT is not defined. Load bootstrap.php first.');
        }

        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            return true;
        }

        if ((time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
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

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function login(array $user): void 
    {
        session_regenerate_id(true);
        $_SESSION['user_id']       = $user['id'];
        $_SESSION['name']          = $user['name'];
        $_SESSION['last_activity'] = time();
    }

    public function destroy(): void 
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 3600,
                $params['path'], 
                $params['domain'],
                $params['secure'], 
                $params['httponly']
            );
        }

        session_destroy();
        session_start();
        $this->csrfGuard->refresh();
    }

    public function user(): ?array 
    {
        if (empty($_SESSION['user_id']))  return null;
        
        return [
            'id'   => (int) $_SESSION['user_id'], // Explicit cast to guarantee an integer type
            'name' => $_SESSION['name'],
        ];
    }

    public function isLoggedIn(): bool 
    {
        return !empty($_SESSION['user_id']);
    }
}