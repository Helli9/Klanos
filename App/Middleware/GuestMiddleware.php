<?php
namespace App\Middleware;

class GuestMiddleware
{
    public function handle(): void
    {
        // If they are logged in, send them to home instead of showing the login box
        if (isset($_SESSION['user']) || !empty($_SESSION['user_id'])) { 
            header('Location: /home');
            exit;
        }
    }
}