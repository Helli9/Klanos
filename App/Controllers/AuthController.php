<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Requests\LoginRequest;
use App\Requests\SignupRequest;
use App\Security\SessionManager;



class AuthController extends Controller{

    public function showLogin()
    {
        $this->view('pages/login', ['errors' => []]);
    }

    public function showSignup()
    {
        $this->view('pages/signup', ['errors' => []]);
    }

    public function __construct(private AuthService $authService) {}


    public function login() 
    {
        // 1. Initialize and Validate
        $request = new LoginRequest($_POST);
        if (!$request->isValid()) {
            return $this->view('pages/login', [
                'errors' => $request->errors(),
                'old'    => $request->all() // Good for repopulating fields
            ]);
        }
        // 2. Attempt Login
        try {
            $user  = $this->authService->login(
                $request->email(),
                $request->password(),
                $_SERVER['REMOTE_ADDR']
            );
            // 4. Success
            SessionManager::start($user);
            return $this->redirect('/home');

        } catch (\RuntimeException $e) {
            return $this->view('pages/login', ['errors' => ['generic' => $e->getMessage()]]);
        }
    }

    public function signup() 
    {
        // 1. Initialize and Validate
        $request = new SignupRequest($_POST);
        if (!$request->isValid()) {
            return $this->view('pages/signup', [
                'errors' => $request->errors(),
                'old'    => $request->all()
            ]);
        }
        // 2. Attempt Login
        try {
            $this->authService->signup(
                $request->name(),
                $request->email(),
                $request->password()
            );
            // 4. Success
            return $this->redirect('/login');
            
        } catch (\RuntimeException $e) {
            return $this->view('pages/signup', ['errors' => ['generic' => $e->getMessage()]]);
        }
    }

    public function logout() 
    {
        SessionManager::destroy();
        $this->redirect('/login');
    }
}