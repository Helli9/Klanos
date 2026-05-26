<?php

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\AuthController;
use App\Services\AuthService;
use App\Security\SessionManager;
use App\Security\CsrfGuard;

class TestableAuthController extends AuthController
{
    public array $renderedViews = [];
    public ?string $redirectPath = null;

    protected function view(string $path, array $data = []): void
    {
        $this->renderedViews[] = [
            'path' => $path,
            'data' => $data,
        ];
    }

    protected function redirect(string $path): void
    {
        $this->redirectPath = $path;
    }
}

class AuthControllerTest extends TestCase
{
    private TestableAuthController $controller;
    private AuthService&MockObject $authService;
    private SessionManager&MockObject $sessionManager;
    private CsrfGuard&MockObject $csrfGuard;

    protected function setUp(): void
    {
        $this->authService    = $this->createMock(AuthService::class);
        $this->sessionManager = $this->createMock(SessionManager::class);
        $this->csrfGuard      = $this->createMock(CsrfGuard::class);

        $this->controller = new TestableAuthController(
            $this->authService,
            $this->sessionManager,
            $this->csrfGuard
        );
    }

    // ------------------------------------------------------------------
    // showLogin
    // ------------------------------------------------------------------

    public function test_showLogin_passes_csrf_token_to_view(): void
    {
        $this->csrfGuard
            ->expects($this->once())
            ->method('get')
            ->willReturn('test-csrf-token');

        $this->controller->showLogin();

        $this->assertCount(1, $this->controller->renderedViews);

        $view = $this->controller->renderedViews[0];

        $this->assertEquals('pages/login', $view['path']);
        $this->assertEquals(
            'test-csrf-token',
            $view['data']['csrfToken']
        );
    }

    // ------------------------------------------------------------------
    // login — validation failure
    // ------------------------------------------------------------------

    public function test_login_renders_login_view_when_validation_fails(): void
    {
        $_POST = [
            'email' => '',
            'password' => '',
        ];

        $this->authService
            ->expects($this->never())
            ->method('login');

        $this->controller->login();

        $this->assertCount(1, $this->controller->renderedViews);

        $view = $this->controller->renderedViews[0];

        $this->assertEquals('pages/login', $view['path']);
    }

    // ------------------------------------------------------------------
    // login — success
    // ------------------------------------------------------------------

    public function test_login_starts_session_and_redirects_on_success(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $_POST = [
            'email' => 'user@example.com',
            'password' => 'secret',
        ];

        $fakeUser = [
            'id' => 1,
            'name' => 'Alice',
        ];

        $this->authService
            ->expects($this->once())
            ->method('login')
            ->with(
                'user@example.com',
                'secret',
                $this->anything()
            )
            ->willReturn($fakeUser);

        $this->sessionManager
            ->expects($this->once())
            ->method('start')
            ->with($fakeUser);

        $this->controller->login();

    $this->assertNotNull($this->controller->redirectPath);
    }

    // ------------------------------------------------------------------
    // login — RuntimeException
    // ------------------------------------------------------------------

    public function test_login_renders_login_view_on_runtime_exception(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $_POST = [
            'email' => 'user@example.com',
            'password' => 'wrong',
        ];

        $this->authService
            ->method('login')
            ->willThrowException(
                new \RuntimeException('Invalid credentials')
            );

        $this->sessionManager
            ->expects($this->never())
            ->method('start');

        $this->controller->login();

        $this->assertCount(1, $this->controller->renderedViews);

        $view = $this->controller->renderedViews[0];

        $this->assertEquals('pages/login', $view['path']);
    }

    // ------------------------------------------------------------------
    // signup — validation failure
    // ------------------------------------------------------------------

    public function test_signup_does_not_call_service_when_validation_fails(): void
    {
        $_POST = [
            'name' => '',
            'email' => '',
            'password' => '',
        ];

        $this->authService
            ->expects($this->never())
            ->method('signup');

        $this->controller->signup();

        $this->assertCount(1, $this->controller->renderedViews);

        $view = $this->controller->renderedViews[0];

        $this->assertEquals('pages/signup', $view['path']);
    }

    // ------------------------------------------------------------------
    // signup — success
    // ------------------------------------------------------------------

    public function test_signup_calls_service_and_redirects_on_success(): void
    {
        $_POST = [
            'name' => 'Alice',
            'email' => 'alice@example.com',          /////to do
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];

        $this->authService
            ->expects($this->once())
            ->method('signup')
            ->with(
                'Alice',
                'alice@example.com',
                'Password1!'
            );

        $this->controller->signup();
        $this->assertEquals('/login', $this->controller->redirectPath);

    }

    // ------------------------------------------------------------------
    // signup — RuntimeException
    // ------------------------------------------------------------------

    public function test_signup_renders_signup_view_on_runtime_exception(): void
    {
        $_POST = [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'Password1!',
        ];

        $this->authService
            ->method('signup')
            ->willThrowException(
                new \RuntimeException('Email already taken')
            );

        $this->controller->signup();

        $this->assertCount(1, $this->controller->renderedViews);

        $view = $this->controller->renderedViews[0];

        $this->assertEquals('pages/signup', $view['path']);
    }

    // ------------------------------------------------------------------
    // logout
    // ------------------------------------------------------------------

    public function test_logout_destroys_session(): void
    {
        $this->sessionManager
            ->expects($this->once())
            ->method('destroy');

        $this->controller->logout();

        $this->assertEquals('/login', $this->controller->redirectPath);
    }

    protected function tearDown(): void
    {
        $_POST = [];
    }
}