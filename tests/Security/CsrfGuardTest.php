<?php
use PHPUnit\Framework\TestCase;
use App\Security\CsrfGuard;

class CsrfGuardTest  extends TestCase
{
     private CsrfGuard $csrfGuard;

    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
        $this->csrfGuard = new CsrfGuard();
    }

    //Cleans up after the last test in case anything else runs after
    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    // ── get()────────────────────────────────
    public function test_get_generates_token_when_session_is_empty(): void
    {
        $token = $this->csrfGuard->get();
        $this->assertNotEmpty($token);
        $this->assertSame($token, $_SESSION['csrf_token']);
    }

    public function test_get_returns_existing_token_when_already_set(): void
    {
        $_SESSION['csrf_token'] = 'existing_token';
        $token = $this->csrfGuard->get();
        $this->assertSame('existing_token', $token);
    }

    // ── refresh()────────────────────────────────
    public function test_refresh_generates_new_token_each_time(): void
    {
         $this->csrfGuard->refresh();
        $first = $_SESSION['csrf_token'];

         $this->csrfGuard->refresh();
        $second = $_SESSION['csrf_token'];

        $this->assertNotSame($first, $second);
    }

    // ── validate()────────────────────────────────
    public function test_validate_returns_true_when_tokens_match(): void
    {
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST['csrf_token']    = 'valid_token';

        $this->assertTrue($this->csrfGuard->validate());
    }

    public function test_validate_returns_false_when_session_token_missing(): void
    {
        $_POST['csrf_token'] = 'valid_token';

        $this->assertFalse($this->csrfGuard->validate());
    }

    public function test_validate_returns_false_when_tokens_do_not_match(): void
    {
        $_SESSION['csrf_token'] = 'valid_token';
        $_POST['csrf_token']    = 'wrong_token';

        $this->assertFalse( $this->csrfGuard->validate());
    }

    public function test_validate_returns_false_when_both_tokens_missing(): void
    {
        $this->assertFalse($this->csrfGuard->validate());
    }
}