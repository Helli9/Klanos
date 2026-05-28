<?php
use PHPUnit\Framework\TestCase;
use App\Middleware\CsrfMiddleware;
use App\Security\CsrfGuard;

// CsrfMiddlewareTest.php
class CsrfMiddlewareTest extends TestCase
{
    
    protected function setUp(): void
    {
        $_POST = [];
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function test_it_passes_on_get_request(): void
    {
        $csrf = $this->createMock(CsrfGuard::class);

        $csrf->expects($this->never())
             ->method('validate');

        $middleware = new CsrfMiddleware($csrf);

        $middleware->handle();

        $this->assertTrue(true);
    }

    public function test_it_passes_with_valid_csrf_token(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $csrf = $this->createMock(CsrfGuard::class);

        $csrf->expects($this->once())
             ->method('validate')
             ->willReturn(true);

        $middleware = new CsrfMiddleware($csrf);

        $middleware->handle();

        $this->assertTrue(true);
    }
}