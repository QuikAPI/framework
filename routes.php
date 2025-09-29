<?php
use QuikAPI\Http\Router;
use QuikAPI\Controllers\HealthController;

/** @var Router $router */
$router->get('/health', [HealthController::class, 'index']);
