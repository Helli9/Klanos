<?php
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\NeedListController;

return [
    'GET' => [
        '/' => [AuthController::class, 'showLogin'],
        '/login' => [AuthController::class, 'showLogin'],
        '/signup' => [AuthController::class, 'showSignup'],
        '/home' =>  [HomeController::class, 'index'],
    ],
    'POST' => [
        '/login' => [AuthController::class, 'login'],
        '/signup' => [AuthController::class, 'signup'],
        '/logout' => [AuthController::class, 'logout'],
        
        // Add these two lines to handle your NeedList actions:
        '/home/create-need' => [NeedListController::class, 'create'],
        '/home/delete-need' => [NeedListController::class, 'delete'],
    ]
];