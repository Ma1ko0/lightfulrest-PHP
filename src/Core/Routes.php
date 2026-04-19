<?php

declare(strict_types=1);

use App\Response;
use App\Router;
use App\Request;
use App\UserController;
use App\Middleware\JsonContentTypeMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\LoggingMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use App\Middleware\SessionMiddleware;
use App\Middleware\ValidationMiddleware;

$pdo = (new Database())->getConnection();
$router = new Router($pdo);

// REST API routes group
$router->group(['prefix' => '/api', 'middleware' => [LoggingMiddleware::class, RateLimitMiddleware::class, JsonContentTypeMiddleware::class, SecurityHeadersMiddleware::class]], function (Router $router) {
    $router->get('/users/(\d+)', [UserController::class, 'getUserDataByID'])->register();
});

// Web routes group
$router->group(['middleware' => [SessionMiddleware::class, ValidationMiddleware::class]], function (Router $router) {
    $router->post('/login', [WebLoginController::class, 'login'])
        ->register();
});

// OpenAPI docs endpoint
$router->get('/openapi.yaml', function (Request $request) {
    header('Content-Type: application/x-yaml');
    echo file_get_contents(__DIR__ . '/../../openapi.yaml');
})->register();

require_once __DIR__ . '/DocsRoutes.php';

// CORS preflight
$router->options('/(.*)', function (Request $request) {
    new Response()->empty();
})->register();

$request = new Request();
$router->dispatch($request);
