<?php

declare(strict_types=1);

namespace App;

use Methods;
use App\Request;
use PDO;

class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    public function applyGroupAttributes(Route $route): void
    {
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $route->prefix($group['prefix']);
            }
            if (isset($group['middleware'])) {
                $route->middleware($group['middleware']);
            }
        }
    }

    public function get(string $pattern, mixed $handler): Route
    {
        return $this->createRoute(Methods::GET, $pattern, $handler);
    }

    public function post(string $pattern, mixed $handler): Route
    {
        return $this->createRoute(Methods::POST, $pattern, $handler);
    }

    public function put(string $pattern, mixed $handler): Route
    {
        return $this->createRoute(Methods::PUT, $pattern, $handler);
    }

    public function patch(string $pattern, mixed $handler): Route
    {
        return $this->createRoute(Methods::PATCH, $pattern, $handler);
    }

    public function delete(string $pattern, mixed $handler): Route
    {
        return $this->createRoute(Methods::DELETE, $pattern, $handler);
    }

    public function options(string $pattern, mixed $handler): Route
    {
        return $this->createRoute(Methods::OPTIONS, $pattern, $handler);
    }

    private function createRoute(Methods|string $method, string $pattern, mixed $handler): Route
    {
        return new Route($this, $method, $pattern, $handler);
    }

    public function addRoute(string|Methods $method, string $pattern, callable $handler): void
    {
        $this->add($method, $pattern, $handler);
    }

    public function add(string|Methods $method, string $pattern, array|callable $handler): void
    {
        if (gettype($method) !== 'object') {
            $method = strtoupper($method);
            $method = Methods::from($method) ?? Methods::UNKNOWN;
        }

        $this->routes[] = [
            'method' => $method,
            'pattern' => "#^" . $pattern . "$#",
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request)
    {
        $method = $request->getMethod();
        $uri = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] == $method && preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);
                $handler = $route['handler'];

                return call_user_func_array($handler, array_merge([$request], $matches));
            }
        }
        $response = new Response();
        $response->error('Route Not Found', 404);
    }
}
