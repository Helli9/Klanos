<?php
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\NeedListController;

$router = new Router();

$router->get('/home', [HomeController::class, 'index']);

$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/signup', [AuthController::class, 'showSignup']);
$router->post('/signup', [AuthController::class, 'signup']);

$router->post('/logout', [AuthController::class, 'logout']);

$router->post('/home/create-need', [NeedListController::class, 'create']);
$router->post('/home/delete-need', [NeedListController::class, 'delete']);

return $router;