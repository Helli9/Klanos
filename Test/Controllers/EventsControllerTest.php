<?php
namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\EventsController;
use App\Services\EventsService;

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
    private EventsController $controller;
    private EventsService&MockObject $eventsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventsService = $this->createMock(EventsService::class);
        $this->controller    = new TestableEventsController($this->eventsService);
    }

    // ------------------------------------------------------------------
    // register — validation failure
    // ------------------------------------------------------------------

    public function test_register_does_not_call_service_when_validation_fails(): void
    {
        $_POST = []; // missing required fields

        $this->eventsService->expects($this->never())->method('register');

        $this->controller->register();
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
        $this->expectNotToPerformAssertions();
        $_POST = [
            'event_id' => '10', 
            'mode' => 'confirmed'
        ];
        $_SESSION['user_id'] = 3;

        $this->eventsService
            ->method('register')
            ->willThrowException(new \RuntimeException('Already registered'));

        // Must not re-throw
        $this->controller->register();
    }

    protected function tearDown(): void
    {
        $_POST = [];
    }
}