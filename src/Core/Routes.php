<?php

use App\Logger;
use App\Response;
use App\Router;
use App\Request;
use App\Route;
use App\UserController;
use App\Middleware\JsonContentTypeMiddleware;
use App\Middleware\RestAuthMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\LoggingMiddleware;
use App\Middleware\SessionMiddleware;
use App\Middleware\AuthMiddleware;

$router = new Router();
Route::setRouter($router);

// REST API routes group
Route::group(['prefix' => '/api', 'middleware' => [LoggingMiddleware::class, RateLimitMiddleware::class, JsonContentTypeMiddleware::class]], function() {
    Route::get('/users/(\d+)', [UserController::class, 'getUserDataByID'])->register();
});

// Web routes group
Route::group(['middleware' => [SessionMiddleware::class]], function() {
    Route::post('/login', [WebLoginController::class, 'login'])
        ->middleware([AuthMiddleware::class])
        ->register();
});

// CORS preflight
Route::options('/(.*)', function (Request $request) {
    Response::empty();
})->register();

$request = new Request();
$router->dispatch($request);

