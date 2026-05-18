<?php
namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        // Make sure this matches exactly how SessionManager::start() saves the user data
        if (!isset($_SESSION['user']) && empty($_SESSION['user_id'])) { 
            header('Location: /login');
            exit;
        }
    }
}