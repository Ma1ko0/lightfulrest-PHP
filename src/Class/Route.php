<?php

declare(strict_types=1);

namespace App;

use Methods;
use Middleware;

class Route
{
    private Router $router;
    private Methods|string $method;
    private string $pattern;
    private mixed $handler;
    private array $middlewares = [];

    public function __construct(Router $router, Methods|string $method, string $pattern, mixed $handler)
    {
        $this->router = $router;
        $this->method = $method;
        $this->pattern = $pattern;
        $this->handler = $handler;
        $this->middlewares = [];

        $this->router->applyGroupAttributes($this);
    }

    public function getMethod(): Methods|string
    {
        return $this->method;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getHandler(): mixed
    {
        return $this->handler;
    }

    public function prefix(string $prefix): void
    {
        $this->pattern = $prefix . $this->pattern;
    }

    public function middleware(array|Middleware $middlewares): self
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

        $this->router->addRoute($this->method, $this->pattern, $handler);
    }

    private function applyMiddlewares(mixed $handler): callable
    {
        return function (Request $request, ...$args) use ($handler) {
            $next = function ($req) use ($handler, $args) {
                if (is_array($handler) && is_string($handler[0]) && is_string($handler[1])) {
                    // Controller
                    $controller = new $handler[0]($req, $this->router->getPdo(), new Logger());
                    return call_user_func_array([$controller, $handler[1]], $args);
                }

                // Function
                return call_user_func_array($handler, array_merge([$req], $args));
            };

            foreach (array_reverse($this->middlewares) as $middleware) {
                $currentNext = $next;
                $next = function ($req) use ($middleware, $currentNext) {
                    $instance = new $middleware();
                    return $instance->handle($req, $currentNext);
                };
            }
            return $next($request);
        };
    }
}
