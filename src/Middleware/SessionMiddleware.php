<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Request;
use Middleware;

class SessionMiddleware extends Middleware
{
    public function handle(Request $request, callable $next)
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Continue
        return $next($request);
    }
}
