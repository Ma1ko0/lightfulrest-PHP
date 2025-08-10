<?php

namespace App;

use Methods;

class Router {
	private array $routes = [];

	/**
	 * Adds a router to the router
	 *
	 * @param string|Methods $method
	 * @param string $pattern
	 * @param array|callable $handler
	 * @return void
	 */
	public function add(string|Methods $method, string $pattern, array|callable $handler): void {

		if (gettype($method) !== "object") {
			$method = strtoupper($method);
			$method = Methods::from($method) ?? Methods::UNKNOWN;
		}
		$this->routes[] = [
			"method" => $method,
			"pattern" => "#^" . $pattern . "$#",
			"handler" => $handler,
		];
	}

	/**
	 * Dispatches the request to the appropriate route handler
	 *
	 * @param string|Methods $method
	 * @param string $uri
	 * @return void
	 */
	public function dispatch(string|Methods $method, string $uri) {
		if (gettype($method) !== "object") {
			$method = strtoupper($method);
			$method = Methods::from($method) ?? Methods::UNKNOWN;
		}
		
		foreach ($this->routes as $route) {
			if ($route["method"] == $method && preg_match($route["pattern"], $uri, $matches)) {
				array_shift($matches);
				$handler = $route["handler"];
				if (
					is_array($handler) &&
					is_string($handler[0]) &&
					is_string($handler[1])
				) {
					$useController = new $handler[0]($method, []);
					return call_user_func_array([$useController, $handler[1]], $matches);
				}
				return call_user_func_array($handler, $matches);
			}
		}
		Response::error("Route Not Found", 404);
	}
}