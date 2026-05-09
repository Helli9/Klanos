<?php
namespace App\Core;

class Controller{
     private const ALLOWED_VIEWS = [
        'pages/login',
        'pages/signup',
        'pages/dashboard',
        'pages/need_lists',
        'layout/home',
    ];

    protected function view(string $path, array $data = []): void {
        if (!in_array($path, self::ALLOWED_VIEWS, strict: true)) {
            http_response_code(404);
            exit('View not found.');
        }
        extract($data);
        require __DIR__ . "/../Views/{$path}.php";
    }

     protected function redirect(string $path): void {
        header("Location: $path");
        exit;
    }

     protected function requireAuth(): void {
        // Session check
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        // CSRF check
        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $this->redirect('/login');
        }
    }
}
?>