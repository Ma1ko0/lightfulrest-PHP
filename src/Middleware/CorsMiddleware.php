<?php

namespace App\Middleware;

use App\Request;
use App\Response;

class CorsMiddleware
{
    public static function handle(Request $request, callable $next)
    {
        // Handle preflight OPTIONS request
        if ($request->getMethod()->value === 'OPTIONS') {
            Response::empty();
            return;
        }

        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        // Continue to next middleware/handler
        return $next($request);
    }
}