<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\HomeService;
use App\Security\CsrfGuard;

class HomeController extends Controller
{
    public function __construct(
        private HomeService $homeService,
        private CsrfGuard $csrfGuard
    ) {}

    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login');
        }

        $data = $this->homeService->getHomeData(
            (int) $_SESSION['user_id'],
            $_GET['category'] ?? null
        );

        $this->view('layout/home', [
            'name' => $_SESSION['name'] ?? 'Guest',
            'csrfToken' => $this->csrfGuard->get(),
            'success'   => $_SESSION['success'] ?? null,
            ...$data
        ]);

        unset($_SESSION['success']);
    }
}