<?php
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\EventsController;
use App\Controllers\NeedListController;
use App\Services\AuthService;
use App\Services\NeedService;
use App\Services\EventsService;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;



// 'uri' => '/login', 'method' => 'POST', 'action' => [AuthController::class, 'login'], 'middleware' => [new CsrfMiddleware()]];
$router = new Router();
// ──Dependency Injection  "DI" bindings ───────────────────────────────────────────────────────
//────────────────────────────────────────────────/the car ──────────/the engine
$router->bind(AuthController::class,     fn() => new AuthController(new AuthService()));
$router->bind(NeedListController::class, fn() => new NeedListController(new NeedService()));
$router->bind(EventsController::class,   fn() => new EventsController(new EventsService()));


// ── Routes ────────────────────────────────────────────────────────────
$router->get('/home', [HomeController::class, 'index'])->middleware([new GuestMiddleware()]);
$router->get('/', [HomeController::class, 'index'])->middleware([new GuestMiddleware()]);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'])->middleware([new CsrfMiddleware()]);

$router->get('/signup', [AuthController::class, 'showSignup']);
$router->post('/signup', [AuthController::class, 'signup'])->middleware([new CsrfMiddleware()]);

$router->post('/logout', [AuthController::class, 'logout'])->middleware([new CsrfMiddleware()]);

$router->post('/home/create-need', [NeedListController::class, 'create'])->middleware([new CsrfMiddleware()]);
$router->post('/home/delete-need', [NeedListController::class, 'delete'])->middleware([new CsrfMiddleware()]);
$router->post('/home/register_events', [EventsController::class, 'register'])->middleware([new CsrfMiddleware()]);

return $router;