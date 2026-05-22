<?php
use PHPUnit\Framework\TestCase;
use App\Core\Router;
use App\Core\Container;

class RouterTest extends TestCase
{
    private Router $router;
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->router = new Router($this->container);
    }

    // ── 1. Correct route is dispatched ────────────────────────────────
    public function test_get_route_dispatches_correct_action(): void
    {
        // ── Arrange ───────────────────────────────────────────────────────
        // stdClass(built-in PHP class) shared log between :
        // the fake controller and the test
        $log = new stdClass();
        $log->called = false;

        //Register--> fake controller that flips $log->called to true when index() runs
        $this->container->set('FakeController', fn() => new class($log) {
            public function __construct(private \stdClass $log) {}
            public function index(): void { $this->log->called = true; }
        });

        // ── Act ───────────────────────────────────────────────────────────
        // Register the route then simulate a GET request to /test
        $this->router->get('/test', ['FakeController', 'index']);
        $this->router->dispatch('/test', 'GET');

        // ── Assert ────────────────────────────────────────────────────────
        // If the router dispatched correctly, index() ran and flipped the flag
        $this->assertTrue($log->called);
    }

    // ── 2. Verifies GET and POST are isolated────────────────────────────────────
    public function test_post_route_does_not_match_get_request(): void
    {
       // ── Arrange ───────────────────────────────────────────────────────
       // Register-->   a fake CONTROLLER in the container so the router can find it.
        $this->container->set('FakeController', fn() => new class {
            public function store(): void {}
        });

        // Register-->   /submit as a POST-only route
        $this->router->post('/submit', ['FakeController', 'store']);

        // ── Act ───────────────────────────────────────────────────────────
        // Capture any output the router echoes
        ob_start();

        // Dispatch the same URI but with GET instead of POST — notFound()
        $this->router->dispatch('/submit', 'GET');

        // Stop capturing and store whatever was echoed
        $output = ob_get_clean();

        // ── Assert ────────────────────────────────────────────────────────
        // notFound() echoes "404 — GET /submit not found"
        $this->assertStringContainsString('404', $output);
    }

    // ── 3. Verifies the 404 handler fires ────────────────────────────────────────
    public function test_unknown_route_returns_404(): void
    {
        // ── Arrange ───────────────────────────────────────────────────────
        // No routes registered at all — any dispatch should hit notFound()

        // ── Act ───────────────────────────────────────────────────────────
        // Start capturing output — notFound() echoes the 404 message
        ob_start();

        // Dispatch a URI that was never registered
        $this->router->dispatch('/does-not-exist', 'GET');

        // Grab the echoed output and stop buffering
        $output = ob_get_clean();

        // ── Assert ────────────────────────────────────────────────────────
        // notFound() echoes "404 — GET /does-not-exist not found"
        $this->assertStringContainsString('404', $output);
    }

    // ── 4. Middleware runs before controller ───────────────────────────
    public function test_middleware_runs_before_controller(): void
    {
        // ── Arrange ───────────────────────────────────────────────────────
        // Using stdClass so both the middleware and controller see the same array
        $log = new stdClass();
        $log->order = [];

        // Fake middleware — pushes 'middleware' into the log when handle() is called
        $fakeMiddleware = new class($log) {
            public function __construct(private \stdClass $log) {}
            public function handle(): void { $this->log->order[] = 'middleware'; }
        };

        // Fake controller — pushes 'controller' into the log when index() is called
        $this->container->set('FakeController', fn() => new class($log) {
            public function __construct(private \stdClass $log) {}
            public function index(): void { $this->log->order[] = 'controller'; }
        });

        // Register the route with the middleware attached
        $this->router->get('/guarded', ['FakeController', 'index'])
                    ->middleware([$fakeMiddleware]);

        // ── Act ───────────────────────────────────────────────────────────
        // Dispatch the route — middleware should run first, then the controller
        $this->router->dispatch('/guarded', 'GET');

        // ── Assert ────────────────────────────────────────────────────────
        // The order array must be exactly ['middleware', 'controller'] —
        // if controller ran first it would be ['controller', 'middleware']
        $this->assertSame(['middleware', 'controller'], $log->order);
    }

    // ── 5. Unbound class throws ────────────────────────────────────────
    public function test_unbound_controller_throws(): void
    {
        // ── Arrange ───────────────────────────────────────────────────────
        // Register--> route pointing to a controller that was never bound in the container 
        $this->router->get('/broken', ['NonExistentController', 'index']);

        // ── Assert ───────────────────────────────────────────────────────
        // Tell PHPUnit we EXPECT a RuntimeException to be thrown
        // If it isn't thrown, the test fails
        $this->expectException(\RuntimeException::class);

        // ── Act ───────────────────────────────────────────────────────────
        // Dispatch the route — router calls 
        //make() → container->get() →container finds no binding for 'NonExistentController' → throws
        $this->router->dispatch('/broken', 'GET');
    }
}