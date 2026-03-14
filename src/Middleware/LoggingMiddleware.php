<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Request;
use App\Logger;
use Middleware;

class LoggingMiddleware extends Middleware
{
    public function handle(Request $request, callable $next)
    {
        // Log the request
        $logger = new Logger();
        $logger->logging(
            sprintf(
                'Request: %s %s from %s',
                $request->getMethod()->value,
                $request->getUri(),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ),
            INFO,
        );

        // Continue
        $response = $next($request);

        // Optionally log response
        $logger->logging('Request processed', INFO);

        return $response;
    }
}
