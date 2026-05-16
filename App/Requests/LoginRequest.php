<?php 
namespace App\Requests;

use App\Core\FormRequest;

class LoginRequest extends FormRequest
{
    protected function validate(): void
    {
        $email = $this->input('email');
        $password = $this->input('password');

        if (empty($email)) {
            $this->errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email format.';
        }

        if (empty($password)) {
            $this->errors['password'] = 'Password is required.';
        }
    }

    public function email(): string
    {
        return $this->input('email');
    }

    public function password(): string
    {
        return $this->input('password');
    }
}
