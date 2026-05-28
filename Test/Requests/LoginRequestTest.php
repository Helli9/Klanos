<?php
use PHPUnit\Framework\TestCase;
use App\Requests\LoginRequest;

class LoginRequestTest extends TestCase
{
    private function makeRequest(array $overrides = []): LoginRequest
    {
        $default = [
            'email'    => 'test@example.com',
            'password' => 'Secret1!',
        ];

        return new LoginRequest(array_merge($default, $overrides));
    }
    
    public function test_valid_input_passes()
    {
        // No overrides — use the default valid data
        $request = $this->makeRequest();

        // Both fields are valid so isValid() should return true
        $this->assertTrue($request->isValid());
    }

    public function test_empty_email_fails()
    {
        // Override email with an     <empty string>
        $request = $this->makeRequest(['email' => '']);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('email', $request->errors());
    }

    public function test_invalid_email_fails()
    {
        // "notanemail" has no @ so filter_var() should reject it
        $request = $this->makeRequest(['email' => 'notanemail']);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('email', $request->errors());
    }

    public function test_valid_email_passes()
    {
        $request = $this->makeRequest(['email' => 'user@domain.com']);

        // No email error should exist
        $this->assertArrayNotHasKey('email', $request->errors());
    }

    public function test_empty_password_fails()
    {
        $request = $this->makeRequest(['password' => '']);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('password', $request->errors());
    }

    public function test_valid_password_passes(): void
    {
        $request = $this->makeRequest(['password' => 'Secret1!']);

        $this->assertArrayNotHasKey('password', $request->errors());
    }

//----------------------------------------------------------------------------
    public function test_email_accessor_returns_correct_value()
    {
        $request = $this->makeRequest(['email' => 'hello@test.com']);

        $this->assertSame('hello@test.com', $request->email());
    }

    public function test_password_accessor_returns_correct_value()
    {
        $request = $this->makeRequest(['password' => 'Secret1!']);

        $this->assertSame('Secret1!', $request->password());
    }
}