<?php

use App\Response;
use App\Router;
use App\UserController;

$router = new Router();
$router->add(Methods::GET, '/users/(\d+)', [UserController::class, 'getUserDataByID']);


// CORS Route
$router->add(Methods::OPTIONS, '/(.*)', function () {
	Response::empty();
});
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($_SERVER["REQUEST_METHOD"], $uri);