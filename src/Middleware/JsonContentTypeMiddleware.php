<?php

namespace App\Middleware;

use App\Request;
use App\Response;

class JsonContentTypeMiddleware
{
    public static function handle(Request $request, callable $next)
    {
        // For POST, PUT, PATCH, check Content-Type
        $method = $request->getMethod()->value;
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $contentType = $request->getHeaders('Content-Type') ?? '';
            if (strpos($contentType, 'application/json') === false) {
                Response::error('Content-Type must be application/json', 400);
                return;
            }
        }

        // Continue
        return $next($request);
    }
}