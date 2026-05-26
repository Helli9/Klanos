<?php
namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): bool
    {
        if (empty($_SESSION['user_id'])) { 
            $this->redirect('/login');
            return false;
        }
        return true;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}