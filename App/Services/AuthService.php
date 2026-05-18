<?php
namespace App\Services;

use App\Models\UserModel;
use App\Models\LoginAttemptModel;

class AuthService {

    public function __construct(private UserModel $users) {}
    
    public function login(string $email, string $password, string $ip): array 
    {
        if (LoginAttemptModel::isLocked($email, $ip))
            throw new \RuntimeException('Too many failed attempts. Try again in 15 minutes.');

        //$user = UserModel::signin($email);
        $user = $this->users->signin($email);
       

        if (!$user || !password_verify($password, $user['password'])) {
            LoginAttemptModel::record($email, $ip);
            throw new \RuntimeException('Incorrect email or password.');
        }

        LoginAttemptModel::clearFor($email);
        return $user;
    }

    public function signup(string $name, string $email, string $password): void 
    {
        
        if (UserModel::findByEmail($email))
            throw new \RuntimeException('This email is already registered.');

        $hashed = password_hash($password, PASSWORD_ARGON2ID);
        //$created = UserModel::create($name, $email, $hashed);
        $created = $this->users->create($name, $email, $hashed);

        if (!$created)
            throw new \RuntimeException('Something went wrong. Please try again.');
    }
}
