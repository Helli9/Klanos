<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Core\Controller;
use App\Models\LoginAttemptModel;


class AuthController extends Controller{

    public function showLogin(){
        $this->view('pages/login', ['errors' => []]);
    }

    public function showSignup(){
        $this->view('pages/signup', ['errors' => []]);
    }


    // ─── LOGIN ────────────────────────────────────────────
    public function login(){
        $errors = [];
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // 1. CSRF VALIDATION
            if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) ||
                !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                $errors['generic'] = "Session expired. Please refresh and try again.";
                return $this->view('pages/login', ['errors' => $errors]);
            }

            $email= trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // 2. BASIC VALIDATION
            if (empty($email)) {
                $errors['email'] = "Please enter your email address";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Please enter a valid email address";
            }
            if(empty($password)) $errors['password'] = "Please enter your password";  

             //3. AUTHENTICATION VERIFICATION
            if (empty($errors)){
                 if (LoginAttemptModel::isLocked($email, $ip)) {
                    $errors['generic'] = "Too many failed attempts. Try again in 15 minutes.";
                    return $this->view('pages/login', ['errors' => $errors]);
                }
                $user= UserModel::signin($email);
               
                if(!$user || !password_verify($password, $user['password'])){
                    LoginAttemptModel::record($email, $ip);
                    $errors['password'] = "Incorrect email or password.";
                }else{
                    LoginAttemptModel::clearFor($email);
                    session_regenerate_id(true);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $this->redirect('/home');
                }
            }
        }
        $this->view('pages/login', ['errors' => $errors,]);
    }

    
    // ─── SIGNUP ────────────────────────────────────────────
    public function signup(){
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
      
            if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) ||
                !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                $errors['generic'] = "Invalid or expired form token. Please try again.";
                return $this->view('pages/signup', ['errors' => $errors]);
            }

            $name =trim($_POST['name'] ?? '');
            $email = trim($_POST['email']  ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirmation = $_POST['password_confirmation'] ?? '';


            if(empty($name) || empty($email) || empty($password) || empty($passwordConfirmation)){
                $errors['name'] = "Please fill in all fields"; 
            }

            // ✅ Email Validation: Check for correct @ and domain format   
            if(! filter_var($email, FILTER_VALIDATE_EMAIL)){
                $errors['email'] = "Please enter a valid email address";
            }

            // ✅ Name Validation: Check Length
            if (!empty($name) && (strlen($name) < 5 || strlen($name) > 25)) {
                $errors['name'] = "Name must be between 5 and 25 characters.";
            }
    
            // ✅ Password Validation: Check Length
            if (strlen($password) < 8 || strlen($password) > 30) {
                $errors['password'] = "Password must be between 8 and 30 characters.";
            }

            // 2. Check Strength (Character Variety)
            $passwordErrors = [];
            if (!preg_match('/[A-Z]/', $password))  $passwordErrors[] = "one uppercase letter";
            if (!preg_match('/[a-z]/', $password))  $passwordErrors[] = "one lowercase letter";
            if (!preg_match('/\d/', $password))     $passwordErrors[] = "one number";
            if (!preg_match('/[\W_]/', $password))  $passwordErrors[] = "one special character";

            if (!empty($passwordErrors)) {
                $errors['password'] = "Password needs: " . implode(', ', $passwordErrors) . ".";
            }

            if($password !== $passwordConfirmation){
                $errors['password_conf'] = "The passwords do not match";
            }

            $existingUser = UserModel::findByEmail($email);
            if($existingUser){
                $errors['email'] = "This email is already registered";
            }


            
            if (empty($errors)){
                $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
                $create = UserModel::create( $name, $email, $hashedPassword);
                if($create){
                    session_regenerate_id(true);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $_SESSION['success'] ="Account created successfully!";
                    header('Location: /login');
                    exit();
                }else{
                    $_SESSION['error'] ="Something went wrong. Please try again.";
                    $this->redirect('/login');
                }
            }
        }
        $this->view('pages/signup', [
            'errors'     => $errors,
        ]);
        
    }
    

    // ─── LOGOUT ────────────────────────────────────────────
    public function logout() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->redirect('/login');
            return;
        }

        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $errors['generic'] = "CSRF validation failed.";
            return $this->redirect('/login');
        }

        $_SESSION = [];

        // Expire the session cookie on the client side
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 3600,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        session_start();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $this->redirect('/login');
    }
}
?>