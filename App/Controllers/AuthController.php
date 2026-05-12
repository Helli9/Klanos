<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Security\CsrfGuard;
use App\Validators\LoginValidator;
use App\Validators\SignupValidator;
use App\Security\SessionManager;


class AuthController extends Controller{

    public function showLogin(){
        $this->view('pages/login', ['errors' => []]);
    }

    public function showSignup(){
        $this->view('pages/signup', ['errors' => []]);
    }
    public function __construct(private AuthService $authService) {}



    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            return $this->view('pages/login', ['errors' => []]);

        if (!CsrfGuard::validate())
            return $this->view('pages/login', ['errors' => [
                'generic' => 'Session expired. Please refresh and try again.'
            ]]);

        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $errors = LoginValidator::validateLoginFields($email, $password);

        if (empty($errors)) {
            $result = $this->authService->login($email, $password, $ip);

            if (isset($result['error'])) {
                $errors['generic'] = $result['error'];
            } else {
                SessionManager::start($result['user']);
                return $this->redirect('/home');
            }
        }
        $this->view('pages/login', ['errors' => $errors]);
    }

    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            return $this->view('pages/signup', ['errors' => []]);

        if (!CsrfGuard::validate())
            return $this->view('pages/signup', ['errors' => [
                'generic' => 'Invalid or expired form token. Please try again.'
            ]]);

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['password_confirmation'] ?? '');

        $errors = SignupValidator::validateSignupFields($name, $email, $password, $confirm);
        if (empty($errors)) {
            $result = $this->authService->signup($name, $email, $password);

            if (isset($result['error'])) {
                $errors['email'] = $result['error'];
            } else {
                return $this->redirect('/login');
            }
        }

        $this->view('pages/signup', ['errors' => $errors]);
    }

    public function logout() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->redirect('/login');
            return;
        }

        if (!CsrfGuard::validate())
            return $this->view('pages/login', ['errors' => [
                'generic' => 'Session expired. Please refresh and try again.'
            ]]);


        SessionManager::destroy();
        $this->redirect('/login');
    }
}