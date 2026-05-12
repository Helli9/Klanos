<?php
namespace App\Validators;

class LoginValidator
{
    public static function validateLoginFields(string $email, string $password): array {
        $errors = [];
        if (empty($email))
            $errors['email'] = 'Please enter your email address.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors['email'] = 'Please enter a valid email address.';
        if (empty($password))
            $errors['password'] = 'Please enter your password.';
        return $errors;
    }

}