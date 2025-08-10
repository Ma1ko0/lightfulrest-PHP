<?php

use App\Router;
use App\UserController;

$router = new Router();
$router->add(Methods::GET, '/users/(\d+)', [UserController::class, 'getUserDataByID']);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($_SERVER["REQUEST_METHOD"], $uri);