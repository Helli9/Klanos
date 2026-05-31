<?php

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\EventsController;
use App\Services\EventsService;
use App\Services\HomeService;
use App\Security\CsrfGuard;

/**
 * SUBCLASS SANDBOX: Intercepts side-effects (redirect/view) so they don't 
 * trigger actual headers, echo output, or kill the script with exit() during validation failures.
 */
class TestableEventsController extends EventsController
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

class EventsControllerTest extends TestCase
{
    private TestableEventsController $controller;
    private EventsService&MockObject $eventsService;
    private HomeService&MockObject $homeService;
    private CsrfGuard&MockObject $csrfGuard;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Initialize all required dependencies as Mock Objects
        $this->eventsService = $this->createMock(EventsService::class);
        $this->homeService   = $this->createMock(HomeService::class);
        $this->csrfGuard     = $this->createMock(CsrfGuard::class);

        // 2. Instantiate the wrapper, satisfying the full constructor signature
        // Note: Change the order of parameters here if your real EventsController constructor differs
        $this->controller = new TestableEventsController(
            $this->eventsService,
            $this->homeService,
            $this->csrfGuard
        );
    }

    // ------------------------------------------------------------------
    // Register Validation Rules
    // ------------------------------------------------------------------

    public function test_register_does_not_call_service_when_validation_fails(): void
    {
        // Arrange: Empty global post array mocks empty validation payload
        $_POST = []; 
        
        $this->homeService
            ->method('getHomeData')
            ->willReturn([]);

        $this->csrfGuard
            ->method('get')
            ->willReturn('fake-csrf-token');

        // Assert: Verify that execution flow halts before service layer manipulation
        $this->eventsService
            ->expects($this->never())
            ->method('register');

        // Act
        $this->controller->register();

        // Assert: Instead of a risky early exit, we verify the redirected sandbox state
        $this->assertNotNull($this->controller->redirectPath, 'Controller should trigger a failure redirection path.');
    }

    public function test_register_calls_service_with_correct_data(): void
    {
        // Arrange
        $_POST = [
            'event_name' => 'Tech Meetup 2026',
            'event_date' => '2026-08-15'
        ];

        $this->eventsService
            ->expects($this->once())
            ->method('register')
            ->with($_POST);

        // Act
        $this->controller->register();
    }

    public function test_register_renders_error_view_on_runtime_exception(): void
    {
        // Arrange
        $_POST = [
            'event_name' => 'Broken Event',
            'event_date' => '2026-01-01'
        ];

        $this->eventsService
            ->method('register')
            ->willThrowException(new \RuntimeException("Database connection failed"));

        // Act
        $this->controller->register();

        // Assert: Intercepted view captures data cleanly
        $this->assertCount(1, $this->controller->renderedViews);
        $this->assertSame('errors/generic', $this->controller->renderedViews[0]['path']);
    }

    protected function tearDown(): void
    {
        // Clean up global superglobals state isolation
        $_POST = [];
        $_GET  = [];
    }
}