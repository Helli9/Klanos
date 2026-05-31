<?php

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\NeedListController;
use App\Services\NeedService;
use App\Security\CsrfGuard;


/**
 * SUBCLASS SANDBOX: Intercepts side-effects (redirect/view) so they don't 
 * trigger actual headers, echo output, or kill the script with exit().
 */
class TestableNeedListController extends NeedListController
{
    public array $renderedViews = [];
    public ?string $redirectPath = null;

    protected function view(string $path, array $data = []): void
    {
        $this->renderedViews[] = ['path' => $path, 'data' => $data];
    }

    protected function redirect(string $path): void 
    {
        $this->redirectPath = $path;
    }
}

class NeedListControllerTest extends TestCase
{
    private TestableNeedListController $controller;
    private NeedService&MockObject $needService;
    private CsrfGuard&MockObject $csrfGuard;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 1. Initialize both required mock dependencies
        $this->needService = $this->createMock(NeedService::class);        $this->csrfGuard     = $this->createMock(CsrfGuard::class);

        // 2. Pass BOTH arguments to satisfy constructor expectations (Expected 2. Found 2.)
        $this->controller  = new TestableNeedListController(
            $this->needService,
            $this->csrfGuard,
        );

        $_SESSION['user_id'] = 5;
    }

    // ------------------------------------------------------------------
    // create — validation failure (empty POST)
    // ------------------------------------------------------------------

    public function test_create_does_not_call_service_when_validation_fails(): void
    {
        $_POST = [];

        $this->needService->expects($this->never())->method('create');

        $this->controller->create();

        $this->assertNotEmpty($this->controller->renderedViews, 'Should render template on layout validation failure');
    }

    // ------------------------------------------------------------------
    // create — success, mode defaults to 'pve'
    // ------------------------------------------------------------------

    public function test_create_calls_service_with_pve_mode_by_default(): void
    {
        $_POST = [
            'category' => 'groceries',
            'item'     => 'milk',
        ];

        $this->needService
            ->expects($this->once())
            ->method('create')
            ->with('groceries', 'milk', 'pve', 5);

        $this->controller->create();

        $this->assertNotNull($this->controller->redirectPath);
    }

    public function test_create_calls_service_with_pvp_mode_when_specified(): void
    {
        $_POST = [
            'category' => 'groceries',
            'item'     => 'milk',
            'mode'     => 'pvp',
        ];

        $this->needService
            ->expects($this->once())
            ->method('create')
            ->with('groceries', 'milk', 'pvp', 5);

        $this->controller->create();

        $this->assertNotNull($this->controller->redirectPath);
    }

    // ------------------------------------------------------------------
    // create — service throws RuntimeException
    // ------------------------------------------------------------------

    public function test_create_renders_error_view_on_runtime_exception(): void
    {
        $_POST = [
            'category' => 'groceries',
            'item'     => 'milk',
        ];

        $this->needService
            ->method('create')
            ->willThrowException(new \RuntimeException('Failed to create'));

        $this->controller->create();

        $this->assertNotEmpty($this->controller->renderedViews);
        $lastView = end($this->controller->renderedViews);
        $this->assertSame('Failed to create', $lastView['data']['errors']['generic']);
    }

    // ------------------------------------------------------------------
    // delete — validation failure (missing need_id)
    // ------------------------------------------------------------------

    public function test_delete_does_not_call_service_when_validation_fails(): void
    {
        $_POST = [];

        $this->needService->expects($this->never())->method('delete');

        $this->controller->delete();

        $this->assertNotEmpty($this->controller->renderedViews);
    }

    // ------------------------------------------------------------------
    // delete — success
    // ------------------------------------------------------------------

    public function test_delete_calls_service_with_correct_data(): void
    {
        $_POST = [
            'need_id' => '99',
        ];

        $this->needService
            ->expects($this->once())
            ->method('delete')
            ->with(99, 5);

        $this->controller->delete();

        $this->assertNotNull($this->controller->redirectPath);
    }

    // ------------------------------------------------------------------
    // delete — service throws RuntimeException
    // ------------------------------------------------------------------

    public function test_delete_renders_error_view_on_runtime_exception(): void
    {
        $_POST = [
            'need_id' => '99',
        ];

        $this->needService
            ->method('delete')
            ->willThrowException(new \RuntimeException('Not found'));

        $this->controller->delete();

        $this->assertNotEmpty($this->controller->renderedViews);
        $lastView = end($this->controller->renderedViews);
        $this->assertSame('Not found', $lastView['data']['errors']['generic']);
    }

    protected function tearDown(): void
    {
        $_POST    = [];
        $_SESSION = [];
        parent::tearDown();
    }
}