<?php
namespace App\Security;

class SessionManager 
{
    public static function start(array $user): void 
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
    }

    public static function destroy(): void 
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 3600,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        session_start();
        CsrfGuard::refresh();
    }

    public static function user(): ?array 
    {
        if (empty($_SESSION['user_id'])) return null;
        return [
            'id'   => (int) $_SESSION['user_id'], // Ensure this is definitely an int
            'name' => $_SESSION['name'],
        ];
    }

    public static function isLoggedIn(): bool 
    {
        return !empty($_SESSION['user_id']);
    }
}