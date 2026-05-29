<?php

use App\Core\Container;
use App\Core\Router;
use App\Models\UserModel;
use App\Models\NeedListModel;
use App\Models\ItemModel;
use App\Models\EventsModel;
use App\Models\LoginAttemptModel;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\EventsController;
use App\Controllers\NeedListController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Security\SessionManager;
use App\Security\CsrfGuard;


// ── Dependency Injection "DI" Container Setup ─────────────────────────
$container = new Container();

// 1. Bind Security
$container->set(CsrfGuard::class, fn() => new CsrfGuard());
$container->set(SessionManager::class, fn($c) => new SessionManager($c->get(CsrfGuard::class)));

// 2. Bind Models
$container->set(UserModel::class, fn() => new UserModel());
$container->set(ItemModel::class, fn() => new ItemModel());
$container->set(NeedListModel::class, fn() => new NeedListModel());
$container->set(LoginAttemptModel::class, fn() => new LoginAttemptModel());
$container->set(EventsModel::class, fn() => new EventsModel());


// ── Routes ────────────────────────────────────────────────────────────
// 3. Pass the container into your Router instance
$router = new Router($container);

// Home Routes -> Protected by AuthMiddleware (Must be logged in)
$router->get('/', [HomeController::class, 'index'])->middleware([new AuthMiddleware()]);
$router->get('/home', [HomeController::class, 'index'])->middleware([new AuthMiddleware()]);

// Auth Routes -> Protected by GuestMiddleware (Logged-in users shouldn't see these)
$router->get('/login', [AuthController::class, 'showLogin'])->middleware([new GuestMiddleware()]);
$router->post('/login', [AuthController::class, 'login'])->middleware([new CsrfMiddleware($container->get(CsrfGuard::class)), new GuestMiddleware()]);

$router->get('/signup', [AuthController::class, 'showSignup'])->middleware([new GuestMiddleware()]);
$router->post('/signup', [AuthController::class, 'signup'])->middleware([new CsrfMiddleware($container->get(CsrfGuard::class)), new GuestMiddleware()]);

// Logout should only be accessible if you are actually logged in
$router->post('/logout', [AuthController::class, 'logout'])->middleware([new CsrfMiddleware($container->get(CsrfGuard::class)), new AuthMiddleware()]);

// Feature Routes (Needs & Events)
$router->post('/home/create-need', [NeedListController::class, 'create'])->middleware([new CsrfMiddleware($container->get(CsrfGuard::class)), new AuthMiddleware()]);
$router->post('/home/delete-need', [NeedListController::class, 'delete'])->middleware([new CsrfMiddleware($container->get(CsrfGuard::class)), new AuthMiddleware()]);
$router->post('/home/register_events', [EventsController::class, 'register'])->middleware([new CsrfMiddleware($container->get(CsrfGuard::class)), new AuthMiddleware()]);


return $router;