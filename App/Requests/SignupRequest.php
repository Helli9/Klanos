<?php 

namespace App\Requests;

use App\Core\FormRequest;

class SignupRequest extends FormRequest
{
    protected function validate(): void
    {
        // 1. Capture inputs into local variables for readability
        $name     = $this->input('name');
        $email    = $this->input('email');
        $password = $this->input('password');
        $confirm  = $this->input('password_confirmation'); // Assuming this name

        // 2. Validate Name
        if (empty($name)) {
            $this->errors['name'] = 'Please enter your name.';
        } elseif (strlen($name) < 5 || strlen($name) > 25) {
            $this->errors['name'] = "Name must be between 5 and 25 characters.";
        }

        // 3. Validate Email
        if (empty($email)) {
            $this->errors['email'] = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email address.';
        }

        // 4. Validate Password
        if (empty($password)) {
            $this->errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8 || strlen($password) > 30) {
            $this->errors['password'] = "Password must be between 8 and 30 characters.";
        } else {
            // Complex requirement check
            $missing = [];
            if (!preg_match('/[A-Z]/', $password)) $missing[] = 'one uppercase letter';
            if (!preg_match('/[a-z]/', $password)) $missing[] = 'one lowercase letter';
            if (!preg_match('/\d/',     $password)) $missing[] = 'one number';
            if (!preg_match('/[\W_]/',  $password)) $missing[] = 'one special character';

            if (!empty($missing)) {
                $this->errors['password'] = 'Password needs: ' . implode(', ', $missing) . '.';
            }
        }

        // 5. Confirm Password
        if ($password !== $confirm) {
            $this->errors['password_conf'] = "The passwords do not match.";
        }
    }

    public function name(): string
    {
        return $this->input('name', '');
    }

    public function email(): string
    {
        return $this->input('email', '');
    }

    public function password(): string
    {
        return $this->input('password', '');
    }
}