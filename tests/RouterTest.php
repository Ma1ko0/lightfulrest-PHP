<?php

include_once __DIR__ . "/../src/Class/Router.php";
include_once __DIR__ . "/../src/Enums/Methods.php";
include_once __DIR__ . "/../src/Core/Request.php";
include_once __DIR__ . "/Fakes/ResponseFake.php";

use PHPUnit\Framework\TestCase;
use App\Router;
use App\Request;
use App\Route;

class RouterTest extends TestCase
{
	public function testAddStoresRoute()
	{
		$router = new Router();
		$router->add('GET', '/test', function () {
			return 'ok';
		});

		$reflection = new \ReflectionClass($router);
		$prop = $reflection->getProperty('routes');
		$prop->setAccessible(true);
		$routes = $prop->getValue($router);

		$this->assertCount(1, $routes);
		$this->assertSame(Methods::GET, $routes[0]['method']);
		$this->assertSame('#^/test$#', $routes[0]['pattern']);
	}

	public function testDispatchCallsHandler()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/hello/World';
		$request = new Request();

		$router = new Router();
		$router->add('GET', '/hello/(\w+)', function (Request $request, $name) {
			return "Hello, $name";
		});

		$result = $router->dispatch($request);
		$this->assertSame('Hello, World', $result);
	}

	public function testDispatchUnknownRouteThrows()
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Route Not Found');

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/no-such-route';
		$request = new Request();

		$router = new Router();
		$router->dispatch($request);
	}

	public function testRouteGroupingAppliesPrefix()
	{
		include_once __DIR__ . "/../src/Class/Route.php";
		$router = new Router();
		Route::setRouter($router);

		Route::group(['prefix' => '/api'], function() {
			Route::get('/users', function(Request $request) {
				return 'users';
			})->register();
		});

		$reflection = new \ReflectionClass($router);
		$prop = $reflection->getProperty('routes');
		$prop->setAccessible(true);
		$routes = $prop->getValue($router);

		$this->assertCount(1, $routes);
		$this->assertSame('#^/api/users$#', $routes[0]['pattern']);
	}
}
