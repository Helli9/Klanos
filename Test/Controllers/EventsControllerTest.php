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
 * trigger actual headers, echo output, or kill the script with exit().
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

        $this->eventsService = $this->createMock(EventsService::class);
        $this->homeService   = $this->createMock(HomeService::class);
        $this->csrfGuard      = $this->createMock(CsrfGuard::class);

        $this->controller    = new TestableEventsController(
            $this->eventsService,
            $this->homeService,
            $this->csrfGuard
        );
    }

    // ------------------------------------------------------------------
    // register — validation failure
    // ------------------------------------------------------------------

    public function test_register_does_not_call_service_when_validation_fails(): void
    {
        // Arrange
        $_POST = []; // missing required fields
        
        // When $_POST is empty, $request->user_id() will likely return null or 0.
        // We set up the mock to accept any parameters passed to it here.
        $this->homeService
            ->method('getHomeData')
            ->willReturn([]);

        $this->csrfGuard
            ->method('get')
            ->willReturn('fake-csrf-token');

        // Assert: Ensure register is never called on the service layer
        $this->eventsService
            ->expects($this->never())
            ->method('register');

        // Act
        $this->controller->register();

        // Assert: Check that the controller correctly rendered the home view with error structures
        $this->assertCount(1, $this->controller->renderedViews, 'Should render home layout on validation failure');
        $this->assertSame('layout/home', $this->controller->renderedViews[0]['path']);
        $this->assertArrayHasKey('errors', $this->controller->renderedViews[0]['data']);
    }

    // ------------------------------------------------------------------
    // register — success
    // ------------------------------------------------------------------

    public function test_register_calls_service_with_correct_data(): void
    {
        // Arrange: 
        $_POST = [
            'event_id' => '10', 
            'mode' => 'confirmed'
        ];
        $_SESSION['user_id'] = 3;

        $this->eventsService
            ->expects($this->once())
            ->method('register')
            ->with(10, 3, 'confirmed');

        // Act 
        $this->controller->register();

        // Assert
        $this->assertEquals('/home?tab=dashboard', $this->controller->redirectPath);
    }

    // ------------------------------------------------------------------
    // register — service throws RuntimeException
    // ------------------------------------------------------------------

    public function test_register_renders_error_view_on_runtime_exception(): void
    {
        // Arrange
        $_POST = [
            'event_id' => '10', 
            'mode' => 'confirmed'
        ];
        $_SESSION['user_id'] = 3;

        $this->homeService
            ->method('getHomeData')
            ->willReturn([]);

        $this->eventsService
            ->method('register')
            ->willThrowException(new \RuntimeException('Already registered'));

        // Act
        $this->controller->register();

        // Assert: Verify it caught the runtime exception and safely re-rendered layout/home
        $this->assertCount(1, $this->controller->renderedViews);
        $this->assertSame('layout/home', $this->controller->renderedViews[0]['path']);
        $this->assertArrayHasKey('errors', $this->controller->renderedViews[0]['data']);
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_SESSION = [];
    }
}