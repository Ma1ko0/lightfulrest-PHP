<?php

namespace App;

use App\Router;
use Methods;

class Route
{
    private Methods|string $method;
    private string $pattern;
    private mixed $handler;
    private array $middlewares = [];
    private static Router $router;
    private static array $groupStack = [];

    public static function group(array $attributes, callable $callback): void
    {
        self::$groupStack[] = $attributes;
        $callback();
        array_pop(self::$groupStack);
    }

    public static function setRouter(Router $router): void
    {
        self::$router = $router;
    }

    public static function get(string $pattern, mixed $handler): self
    {
        return new self(Methods::GET, $pattern, $handler);
    }

    public static function post(string $pattern, mixed $handler): self
    {
        return new self(Methods::POST, $pattern, $handler);
    }

    public static function put(string $pattern, mixed $handler): self
    {
        return new self(Methods::PUT, $pattern, $handler);
    }

    public static function patch(string $pattern, mixed $handler): self
    {
        return new self(Methods::PATCH, $pattern, $handler);
    }

    public static function delete(string $pattern, mixed $handler): self
    {
        return new self(Methods::DELETE, $pattern, $handler);
    }

    public static function options(string $pattern, mixed $handler): self
    {
        return new self(Methods::OPTIONS, $pattern, $handler);
    }

    private function __construct(Methods|string $method, string $pattern, mixed $handler)
    {
        $this->method = $method;
        $this->pattern = $pattern;
        $this->handler = $handler;
        $this->middlewares = [];

        // Apply group attributes
        foreach (self::$groupStack as $group) {
            if (isset($group['prefix'])) {
                $this->pattern = $group['prefix'] . $this->pattern;
            }
            if (isset($group['middleware'])) {
                $middlewares = $group['middleware'];
                if (!is_array($middlewares)) {
                    $middlewares = [$middlewares];
                }
                $this->middlewares = array_merge($this->middlewares, $middlewares);
            }
        }
    }

    public function middleware(array|string $middlewares): self
    {
        if (!is_array($middlewares)) {
            $middlewares = [$middlewares];
        }
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        return $this;
    }

    public function register(): void
    {
        $handler = $this->handler;
        if (!empty($this->middlewares)) {
            $handler = $this->applyMiddlewares($handler);
        }
        self::$router->add($this->method, $this->pattern, $handler);
    }

    private function applyMiddlewares(mixed $handler): callable
    {
        return function(Request $request, ...$args) use ($handler) {
            $next = function($req) use ($handler, $args) {
                if (is_array($handler) && is_string($handler[0]) && is_string($handler[1])) {
                    // Controller
                    $controller = new $handler[0]($req);
                    return call_user_func_array([$controller, $handler[1]], $args);
                } else {
                    // Function
                    return call_user_func_array($handler, array_merge([$req], $args));
                }
            };

            foreach (array_reverse($this->middlewares) as $middleware) {
                $currentNext = $next;
                $next = function($req) use ($middleware, $currentNext) {
                    return $middleware::handle($req, $currentNext);
                };
            }
            return $next($request);
        };
    }
}