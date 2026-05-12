<?php
namespace App\Services;

use App\Models\UserModel;
use App\Models\LoginAttemptModel;

// App/Services/AuthService.php
class AuthService {
    public function login(string $email, string $password, string $ip): array {
        if (LoginAttemptModel::isLocked($email, $ip))
            return ['error' => 'Too many failed attempts. Try again in 15 minutes.'];

        $user = UserModel::signin($email);

        if (!$user || !password_verify($password, $user['password'])) {
            LoginAttemptModel::record($email, $ip);
            return ['error' => 'Incorrect email or password.'];
        }

        LoginAttemptModel::clearFor($email);
        return ['user' => $user];
    }

    public function signup(string $name, string $email, string $password): array {
        if (UserModel::findByEmail($email))
            return ['error' => 'This email is already registered.'];

        $hashed = password_hash($password, PASSWORD_ARGON2ID);
        $created = UserModel::create($name, $email, $hashed);

        return $created
            ? ['success' => true]
            : ['error' => 'Something went wrong. Please try again.'];
    }
}
