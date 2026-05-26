<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\HomeService;

class HomeController extends Controller
{
    public function __construct(
        private HomeService $homeService
    ) {}

    public function index(): void
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->response->redirect('/login');
        }

        $data = $this->homeService->getHomeData(
            (int) $_SESSION['user_id'],
            $_GET['category'] ?? null
        );

        $this->view('layout/home', [
            'name' => $_SESSION['name'] ?? 'Guest',
            ...$data
        ]);
    }
}