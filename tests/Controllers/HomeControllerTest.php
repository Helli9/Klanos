<?php
namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\HomeController;
use App\Services\HomeService;

/**
 * SUBCLASS SANDBOX: Intercepts side-effects (redirect/view) so they don't 
 * trigger actual headers, echo output, or kill the script with exit().
 */
class TestableHomeController extends HomeController
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

class HomeControllerTest extends TestCase
{
    private TestableHomeController $controller; // Use the testable subclass wrapper
    private HomeService&MockObject $homeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->homeService = $this->createMock(HomeService::class);
        $this->controller = new TestableHomeController($this->homeService);
    }

    // ------------------------------------------------------------------
    // index — unauthenticated
    // ------------------------------------------------------------------

    public function test_index_redirects_when_user_not_in_session(): void /////to do
    {
         // Arrange
        unset($_SESSION['user_id']);
        
        // Assert: Service shouldn't be touched if unauthenticated
        $this->homeService->expects($this->never())->method('getHomeData');

        // Act
        $this->controller->index();

        // Assert: Captured redirect path matches expectation
        $this->assertSame('/login', $this->controller->redirectPath);
    }

    // ------------------------------------------------------------------
    // index — authenticated
    // ------------------------------------------------------------------

    public function test_index_calls_service_with_session_user_id(): void
    {
        $_SESSION['user_id'] = '42';
        $_GET = [];
        $fakeData = ['events' => [], 'needs' => []];

        // Assert: Service gets called with cast integer and null category
        $this->homeService
            ->expects($this->once())
            ->method('getHomeData')
            ->with(42, null)
            ->willReturn($fakeData);

        $this->controller->index();
    }

    public function test_index_passes_category_from_get_param(): void
    {
        $_SESSION['user_id'] = '7';
        $_GET['category']    = 'food';

        // Assert: Query parameter 'category' is passed correctly to service
        $this->homeService
            ->expects($this->once())
            ->method('getHomeData')
            ->with(7, 'food')
            ->willReturn([]);

        $this->controller->index();
    }

    public function test_index_uses_guest_name_when_name_not_in_session(): void
    {
        $_SESSION['user_id'] = '1';
        unset($_SESSION['name']);
        $_GET = [];

        $this->homeService->method('getHomeData')->willReturn([]);

        $this->controller->index();

        // Fix risky test: Assert against intercepted view data array
        $this->assertCount(1, $this->controller->renderedViews);
        $this->assertSame('Guest', $this->controller->renderedViews[0]['data']['name']);
    }

    protected function tearDown(): void
    {
        // Clean up global states after each test run
        $_SESSION = [];
        $_GET     = [];
    }
}