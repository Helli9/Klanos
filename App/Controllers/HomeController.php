<?php
namespace App\Controllers;
use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $this->view('layout/home', [
            'name' => $_SESSION['name'] ?? 'Guest'
        ]);
    }
}

