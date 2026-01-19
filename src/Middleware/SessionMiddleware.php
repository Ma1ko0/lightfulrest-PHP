<?php

namespace App\Middleware;

use App\Request;

class SessionMiddleware
{
    public static function handle(Request $request, callable $next)
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Continue
        return $next($request);
    }
}