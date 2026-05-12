<?php
namespace App\Validators;

class SignupValidator {

     public static function validateSignupFields(string $name, string $email, string $password, string $confirm): array {
        $errors = [];

        if (empty($name))
            $errors['name'] = 'Please enter your name.';
        elseif (!empty($name) && (strlen($name) < 5 || strlen($name) > 25)) 
            $errors['name'] = "Name must be between 5 and 25 characters.";

        if (empty($email))
            $errors['email'] = 'Please enter your email address.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors['email'] = 'Please enter a valid email address.';

        if (strlen($password) < 8 || strlen($password) > 30) {
            $errors[] = "Password must be between 8 and 30 characters.";
        } else {
            $missing = [];
            if (!preg_match('/[A-Z]/', $password)) $missing[] = 'one uppercase letter';
            if (!preg_match('/[a-z]/', $password)) $missing[] = 'one lowercase letter';
            if (!preg_match('/\d/',    $password)) $missing[] = 'one number';
            if (!preg_match('/[\W_]/', $password)) $missing[] = 'one special character';
 
            if (!empty($missing))
                $errors['password'] = 'Password needs: ' . implode(', ', $missing) . '.';
        }

        if($password !== $confirm)
            $errors['password_conf'] = "The passwords do not match";
            
        return $errors;
    }
}