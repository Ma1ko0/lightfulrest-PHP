<?php

include_once __DIR__ . "/../src/Class/Router.php";
include_once __DIR__ . "/../src/Enums/Methods.php";
include_once __DIR__ . "/Fakes/ResponseFake.php";

use PHPUnit\Framework\TestCase;
use App\Router;

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
		$router = new Router();
		$router->add('GET', '/hello/(\w+)', function ($name) {
			return "Hello, $name";
		});

		$result = $router->dispatch('GET', '/hello/World');
		$this->assertSame('Hello, World', $result);
	}

	public function testDispatchUnknownRouteThrows()
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Route Not Found');

		$router = new Router();
		$router->dispatch('GET', '/no-such-route');
	}
}
