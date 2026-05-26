<?php
namespace App\Middleware;

class GuestMiddleware
{
    public function handle(): bool
    {
        if (!empty($_SESSION['user_id'])) { 
            $this->redirect('/home');
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