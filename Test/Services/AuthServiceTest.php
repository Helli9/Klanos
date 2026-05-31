<?php

namespace Tests\Unit\Services;

use App\Models\LoginAttemptModel;
use App\Models\UserModel;
use App\Services\AuthService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AuthServiceTest extends TestCase
{
    private $userModelMock;
    private $loginAttemptModelMock;
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create native PHPUnit test doubles
        $this->userModelMock = $this->createMock(UserModel::class);
        $this->loginAttemptModelMock = $this->createMock(LoginAttemptModel::class);

        // 2. Inject them into your service
        $this->authService = new AuthService(
            $this->userModelMock,
            $this->loginAttemptModelMock
        );
    }

    // ====================================================================
    // LOGIN TESTS
    // ====================================================================

    public function test_login_throws_exception_if_account_is_locked(): void
    {
        $this->loginAttemptModelMock
            ->expects($this->once())
            ->method('isLocked')
            ->with('test@example.com', '127.0.0.1')
            ->willReturn(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Too many failed attempts. Try again in 15 minutes.');

        $this->authService->login('test@example.com', 'password123', '127.0.0.1');
    }

    public function test_login_throws_exception_if_user_not_found(): void
    {
        $this->loginAttemptModelMock
            ->expects($this->once())
            ->method('isLocked')
            ->willReturn(false);

        $this->userModelMock
            ->expects($this->once())
            ->method('signin')
            ->with('test@example.com')
            ->willReturn(null); // Simulated: User not found

        // Expect the service to log this bad attempt
        $this->loginAttemptModelMock
            ->expects($this->once())
            ->method('record')
            ->with('test@example.com', '127.0.0.1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Incorrect email or password.');

        $this->authService->login('test@example.com', 'password123', '127.0.0.1');
    }

    public function test_login_throws_exception_on_invalid_password(): void
    {
        $this->loginAttemptModelMock
            ->method('isLocked')
            ->willReturn(false);

        // Generate a hash that will NOT match 'correct_password'
        $wrongHash = password_hash('wrong_password', PASSWORD_ARGON2ID);
        
        $this->userModelMock
            ->method('signin')
            ->willReturn(['email' => 'test@example.com', 'password' => $wrongHash]);

        // Expect the service to log this bad attempt
        $this->loginAttemptModelMock
            ->expects($this->once())
            ->method('record')
            ->with('test@example.com', '127.0.0.1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Incorrect email or password.');

        $this->authService->login('test@example.com', 'correct_password', '127.0.0.1');
    }

    public function test_login_succeeds_and_clears_attempts(): void
    {
        $this->loginAttemptModelMock
            ->method('isLocked')
            ->willReturn(false);

        $correctHash = password_hash('secure123', PASSWORD_ARGON2ID);
        $userData = ['id' => 1, 'email' => 'test@example.com', 'password' => $correctHash];

        $this->userModelMock
            ->expects($this->once())
            ->method('signin')
            ->with('test@example.com')
            ->willReturn($userData);

        // Verify history is cleared on successful login
        $this->loginAttemptModelMock
            ->expects($this->once())
            ->method('clearFor')
            ->with('test@example.com');

        $result = $this->authService->login('test@example.com', 'secure123', '127.0.0.1');

        $this->assertEquals($userData, $result);
    }

    // ====================================================================
    // SIGNUP TESTS
    // ====================================================================

    public function test_signup_throws_exception_if_email_exists(): void
    {
        $this->userModelMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with('existing@example.com')
            ->willReturn(['id' => 1]); // Simulated: Email exists

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This email is already registered.');

        $this->authService->signup('John Doe', 'existing@example.com', 'password123');
    }

    public function test_signup_throws_exception_if_creation_fails(): void
    {
        $this->userModelMock
            ->method('findByEmail')
            ->willReturn(null);

        $this->userModelMock
            ->expects($this->once())
            ->method('create')
            ->with(
                'John Doe', 
                'new@example.com', 
            )
            ->willReturn(false); // Simulated: DB error

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Something went wrong. Please try again.');

        $this->authService->signup('John Doe', 'new@example.com', 'password123');
    }

    public function test_signup_succeeds(): void
    {
        $this->userModelMock
            ->method('findByEmail')
            ->willReturn(null);

        $this->userModelMock
            ->expects($this->once())
            ->method('create')
            ->with(
                'John Doe',
                'new@example.com',
                $this->callback(function ($hashedPassword) {
                    // Custom callback assertion to make sure the string is a valid hash of 'password123'
                    return password_verify('password123', $hashedPassword);
                })
            )
            ->willReturn(true);

        // Act & Assert (Execution completes cleanly without throwing an exception)
        $this->authService->signup('John Doe', 'new@example.com', 'password123');
    }
}