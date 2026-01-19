<?php

namespace App\Middleware;

use App\Request;
use App\Response;

class RateLimitMiddleware
{
    private static array $requests = [];

    public static function handle(Request $request, callable $next)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $currentTime = time();

        // Simple in-memory rate limiting: 100 requests per minute per IP
        if (!isset(self::$requests[$ip])) {
            self::$requests[$ip] = [];
        }

        // Remove old requests (older than 60 seconds)
        self::$requests[$ip] = array_filter(self::$requests[$ip], function($time) use ($currentTime) {
            return ($currentTime - $time) < 60;
        });

        if (count(self::$requests[$ip]) >= 100) {
            Response::error('Rate limit exceeded', 429);
            return;
        }

        // Add current request
        self::$requests[$ip][] = $currentTime;

        // Continue
        return $next($request);
    }
}