<?php
use QuikAPI\Http\Router;
use QuikAPI\Controllers\HealthController;
use QuikAPI\Controllers\UserController;
use QuikAPI\Controllers\AuthController;

/** @var Router $router */
$router->get('/health', [HealthController::class, 'index']);

// Users resource
$router->get('/users', [UserController::class, 'index']);
$router->post('/users', [UserController::class, 'store']);
$router->get('/users/{id}', [UserController::class, 'show']);
$router->put('/users/{id}', [UserController::class, 'update']);
$router->delete('/users/{id}', [UserController::class, 'destroy']);

// Auth
$router->post('/auth/login', [AuthController::class, 'login']);
