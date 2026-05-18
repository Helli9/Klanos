<?php

use App\Core\Container;
use App\Core\Router;
use App\Models\UserModel;
use App\Services\AuthService;
use App\Services\NeedService;
use App\Services\EventsService;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\EventsController;
use App\Controllers\NeedListController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

// ── Dependency Injection "DI" Container Setup ─────────────────────────
$container = new Container();

// 1. Bind Models
$container->set(UserModel::class, fn() => new UserModel());

// 2. Bind Services 
$container->set(AuthService::class, fn($c) => new AuthService($c->get(UserModel::class)));
$container->set(NeedService::class, fn($c) => new NeedService($c->get(UserModel::class)));
$container->set(EventsService::class, fn($c) => new EventsService($c->get(UserModel::class)));

// 3. Bind Controllers
$container->set(HomeController::class, fn() => new HomeController());
$container->set(AuthController::class, fn($c) => new AuthController($c->get(AuthService::class)));
$container->set(NeedListController::class, fn($c) => new NeedListController($c->get(NeedService::class)));
$container->set(EventsController::class, fn($c) => new EventsController($c->get(EventsService::class)));

// 4. Pass the container into your Router instance
$router = new Router($container);


// ── Routes ────────────────────────────────────────────────────────────

// Home Routes -> Protected by AuthMiddleware (Must be logged in)
$router->get('/', [HomeController::class, 'index'])->middleware([new AuthMiddleware()]);
$router->get('/home', [HomeController::class, 'index'])->middleware([new AuthMiddleware()]);

// Auth Routes -> Protected by GuestMiddleware (Logged-in users shouldn't see these)
$router->get('/login', [AuthController::class, 'showLogin'])->middleware([new GuestMiddleware()]);
$router->post('/login', [AuthController::class, 'login'])->middleware([new CsrfMiddleware(), new GuestMiddleware()]);

$router->get('/signup', [AuthController::class, 'showSignup'])->middleware([new GuestMiddleware()]);
$router->post('/signup', [AuthController::class, 'signup'])->middleware([new CsrfMiddleware(), new GuestMiddleware()]);

// Logout should only be accessible if you are actually logged in
$router->post('/logout', [AuthController::class, 'logout'])->middleware([new CsrfMiddleware(), new AuthMiddleware()]);

// Feature Routes (Needs & Events)
$router->post('/home/create-need', [NeedListController::class, 'create'])->middleware([new CsrfMiddleware()]);
$router->post('/home/delete-need', [NeedListController::class, 'delete'])->middleware([new CsrfMiddleware()]);
$router->post('/home/register_events', [EventsController::class, 'register'])->middleware([new CsrfMiddleware()]);


return $router;