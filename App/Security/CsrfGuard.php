<?php
namespace App\Security;

class CsrfGuard {
    public static function validate(): bool {
        return !empty($_POST['csrf_token'])
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }

    public static function refresh(): void {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}