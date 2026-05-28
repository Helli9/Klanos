<?php
use PHPUnit\Framework\TestCase;
use App\Security\SessionManager;
use App\Security\CsrfGuard;

class SessionManagerTest  extends TestCase
{
    private SessionManager $sessionManager;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->sessionManager = new SessionManager(new CsrfGuard());
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    // ── start()────────────────────────────────
    public function test_start_sets_user_id_in_session(): void
    {
        $this->sessionManager->start(['id' => 1, 'name' => 'John']);
        $this->assertSame(1, $_SESSION['user_id']);
    }

    public function test_start_sets_name_in_session(): void
    {
        $this->sessionManager->start(['id' => 1, 'name' => 'John']);
        $this->assertSame('John', $_SESSION['name']);
    }

    // ── user()────────────────────────────────
    public function test_user_returns_array_when_logged_in(): void
    {
        $_SESSION['user_id'] = 5;
        $_SESSION['name']    = 'John';

        $user = $this->sessionManager->user();
        $this->assertSame(['id' => 5, 'name' => 'John'], $user);
    }

    public function test_user_returns_null_when_not_logged_in(): void
    {
        $this->assertNull($this->sessionManager->user());
    }

    // ── isLoggedIn()────────────────────────────────
    public function test_is_logged_in_returns_true_when_user_id_set(): void
    {
        $_SESSION['user_id'] = 1;

        $this->assertTrue($this->sessionManager->isLoggedIn());
    }

    public function test_is_logged_in_returns_false_when_session_empty(): void
    {
        $this->assertFalse($this->sessionManager->isLoggedIn());
    }

    public function test_is_logged_in_returns_false_when_user_id_is_empty(): void
    {
        $_SESSION['user_id'] = '';

        $this->assertFalse($this->sessionManager->isLoggedIn());
    }
}