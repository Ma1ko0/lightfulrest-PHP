<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Request;
use Middleware;

class SecurityHeadersMiddleware extends Middleware
{
    public function handle(Request $request, callable $next)
    {
        // Security headers
        header('X-Frame-Options: DENY'); // Prevent clickjacking
        header('X-Content-Type-Options: nosniff'); // Prevent MIME sniffing
        header('X-XSS-Protection: 1; mode=block'); // XSS protection
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains'); // HSTS
        header('Content-Security-Policy: default-src \'self\''); // Basic CSP
        header('Referrer-Policy: strict-origin-when-cross-origin'); // Referrer policy

        // Continue to next middleware/handler
        return $next($request);
    }
}
