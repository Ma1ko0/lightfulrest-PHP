<?php

namespace App\Middleware;

use App\Request;
use App\Logger;

class LoggingMiddleware
{
    public static function handle(Request $request, callable $next)
    {
        // Log the request
        Logger::logging(
            sprintf('Request: %s %s from %s', 
                $request->getMethod()->value, 
                $request->getUri(), 
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ),
            INFO
        );

        // Continue
        $response = $next($request);

        // Optionally log response
        Logger::logging('Request processed', INFO);

        return $response;
    }
}