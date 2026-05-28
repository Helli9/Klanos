<?php
use PHPUnit\Framework\TestCase;
use App\Requests\SignupRequest;

class SignupRequestTest extends TestCase
{
    private function makeRequest(array $overrides = []): SignupRequest
    {
        $default = [
            'name'                  => 'JohnDoe',
            'email'                 => 'test@example.com',
            'password'              => 'Secret1!',
            'password_confirmation' => 'Secret1!',
        ];

        return new SignupRequest(array_merge($default, $overrides));
    }

    public function test_valid_input_passes(): void
    {
        $request = $this->makeRequest();
        $this->assertTrue($request->isValid());
    }

//Name
    public function test_empty_name_fails(): void
    {
        $request = $this->makeRequest(['name' => '']);
        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('name', $request->errors());
    }

    public function test_invalid_name_fails(): void
    {
        $request = $this->makeRequest(['name' => 'vv']);
        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('name', $request->errors());
    }

    public function test_valid_name_passes(): void
    {
        $request = $this->makeRequest(['name' => 'newNamefdd']);
        $this->assertTrue($request->isValid());
        $this->assertArrayNotHasKey('name', $request->errors());
    }
//Email
    public function test_empty_email_fails(): void
    {
        $request = $this->makeRequest(['email' => '']);
        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('email', $request->errors());
    }

    public function test_invalid_email_fails(): void
    {
        $request = $this->makeRequest(['email' => 'notanemail']);
        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('email', $request->errors());
    }

    public function test_valid_email_passes(): void
    {
        $request = $this->makeRequest(['email' => 'user@domain.com']);
        $this->assertArrayNotHasKey('email', $request->errors());
    }
//Password

    public function test_empty_password_fails() : void
    {
        $request = $this->makeRequest(['password' => '']);
        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('password', $request->errors());
    }

    public function test_password_missing_uppercase_fails(): void 
    {
        $request = $this->makeRequest(['password' => 'secret1!']);
        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('password', $request->errors());
    }

    public function test_password_missing_lowercase_fails(): void 
    {
        $request = $this->makeRequest(['password' => 'SECRET1!']);
        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('password', $request->errors());
    }

    public function test_password_missing_number_fails(): void 
    {
        $request = $this->makeRequest(['password' => 'SECRET!']);
        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('password', $request->errors());
    }
    public function test_password_missing_special_char_fails(): void 
    {
        $request = $this->makeRequest(['password' => 'SECRET1']);
        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('password', $request->errors());

    }

    public function test_valid_password_passes(): void
    {
        $request = $this->makeRequest(['password' => 'Secret1!']);
        $this->assertArrayNotHasKey('password', $request->errors());
    }

//Confirm Password

    public function test_confirm_password_fails(): void
    {
        $request = $this->makeRequest(['password_confirmation' => 'cscsscs']);
        $this->assertArrayHasKey('password_confirmation', $request->errors());
    }

    public function test_valid_confirm_password_passes(): void
    {
        $request = $this->makeRequest(['password_confirmation' => 'Secret1!']);
        $this->assertTrue($request->isValid());
        $this->assertArrayNotHasKey('password_confirmation', $request->errors());
    }

//----------------------------------------------------------------------------
    public function test_name_accessor_returns_correct_value(): void
    {
        $request = $this->makeRequest(['name' => 'JohnDoe']);
        $this->assertSame('JohnDoe', $request->name());
    }
    
    public function test_email_accessor_returns_correct_value(): void
    {
        $request = $this->makeRequest(['email' => 'test@example.com']);
        $this->assertSame('test@example.com', $request->email());
    }

    public function test_password_accessor_returns_correct_value(): void
    {
        $request = $this->makeRequest(['password' => 'Secret1!']);
        $this->assertSame('Secret1!', $request->password());
    }
}