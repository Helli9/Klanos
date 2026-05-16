<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Security\SessionManager;
use App\Requests\LoginRequest;
use App\Requests\SignupRequest;



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
        $result = $this->authService->login(
            $request->email(),
            $request->password(),
            $_SERVER['REMOTE_ADDR']
        );

        // 3. Handle Service Errors 
        if (isset($result['error'])) {
            return $this->view('pages/login', ['errors' => ['generic' => $result['error']]]);
        }

        // 4. Success
        SessionManager::start($result['user']);
        return $this->redirect('/home');
    }

    public function signup() 
    {

        $request = new SignupRequest($_POST);

        if (!$request->isValid()) {
            return $this->view('pages/signup', [
                'errors' => $request->errors(),
                'old'    => $request->all()
            ]);
        }

        $result = $this->authService->signup(
            $request->name(),
            $request->email(),
            $request->password()
        );

        if (isset($result['error'])) {
            return $this->view('pages/signup', [
                'errors' => ['email' => $result['error']]
            ]);
        }

        return $this->redirect('/login');
    }

    public function logout() 
    {
        SessionManager::destroy();
        $this->redirect('/login');
    }
}