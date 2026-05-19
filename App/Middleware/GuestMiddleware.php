<?php
namespace App\Middleware;

class GuestMiddleware
{
    public function handle(): void
    {
        if (!empty($_SESSION['user_id'])) { 
            header('Location: /home');
            exit;
        }
    }
}