<?php
require __DIR__ . '/bootstrap.php';

use QuikAPI\Http\Request;
use QuikAPI\Http\Response;
use QuikAPI\Http\Router;
use QuikAPI\Middleware\Cors;
use QuikAPI\Middleware\ErrorHandler;

$request = new Request();
$router = new Router();

// Global middleware
$router->use(new ErrorHandler());
$router->use(new Cors());

// Load routes
require __DIR__ . '/routes.php';

// Dispatch and output JSON
$result = $router->dispatch($request);
Response::json(is_array($result) ? $result : ['data' => $result]);
