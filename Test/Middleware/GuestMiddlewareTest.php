<?php
use PHPUnit\Framework\TestCase;
use App\Middleware\GuestMiddleware;

class GuestMiddlewareTest extends TestCase
{
    private GuestMiddleware $middleware;

    protected function setUp(): void
    {
        $_SESSION = [];

        // Partial mock to prevent actual header()/exit calls
        $this->middleware = $this->getMockBuilder(GuestMiddleware::class)
            ->onlyMethods(['redirect'])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function test_handle_returns_false_when_user_id_set(): void
    {
        $_SESSION['user_id'] = 5;
        $result = $this->middleware->handle();
        $this->assertFalse($result);
    }

    public function test_handle_returns_true_when_user_id_is_zero(): void
    {
        $_SESSION['user_id'] = 0; // falsy — treated as empty
        $result = $this->middleware->handle();
        $this->assertTrue($result);
    }

        public function test_handle_returns_true_when_session_empty(): void
    {
        $result = $this->middleware->handle();
        $this->assertTrue($result);
    }


}