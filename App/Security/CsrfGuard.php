<?php
namespace App\Security;

class CsrfGuard {

    public function get(): string 
    {
        if (empty($_SESSION['csrf_token'])) {
            $this->refresh();
        }
        return $_SESSION['csrf_token'];
    }

    public function validate() : bool
    {
        return !empty($_POST['csrf_token'])
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
            
        if ($valid) {
                $this->refresh();
            }

        return $valid;
    }

    public function refresh(): void 
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}