<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Request;
use App\Response;
use Middleware;

class JsonContentTypeMiddleware extends Middleware
{
    public function handle(Request $request, callable $next)
    {
        // For POST, PUT, PATCH, check Content-Type
        $method = $request->getMethod()->value;
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $contentType = $request->getHeaders('Content-Type') ?? '';
            if (strpos($contentType, 'application/json') === false) {
                new Response()->error('Content-Type must be application/json', 400);
                return;
            }
        }

        // Continue
        return $next($request);
    }
}
