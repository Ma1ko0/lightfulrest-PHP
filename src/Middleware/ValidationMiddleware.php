<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Request;
use App\Response;
use Middleware;

class ValidationMiddleware extends Middleware
{
    public function handle(Request $request, callable $next)
    {
        $bodyString = $request->getBody();
        if ($bodyString === null) {
            return $next($request); // No body to validate
        }

        $body = json_decode($bodyString, true);
        if ($body === null) {
            return $next($request); // Invalid/missing JSON, skip validation
        }

        // Validate email if present
        if (isset($body['email'])) {
            if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
                new Response()->error("Invalid email format", 422);
                return;
            }
        }

        // Validate username if present (example: alphanumeric, 3-50 chars)
        if (isset($body['username'])) {
            if (!preg_match('/^[a-zA-Z0-9]{3,50}$/', $body['username'])) {
                new Response()->error("Invalid username: must be 3-50 alphanumeric characters", 422);
                return;
            }
        }

        // Validate password if present (example: min 8 chars)
        if (isset($body['password'])) {
            if (strlen($body['password']) < 8) {
                new Response()->error("Password must be at least 8 characters", 422);
                return;
            }
        }

        return $next($request);
    }
}
