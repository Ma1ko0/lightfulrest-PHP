<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Request;
use App\Response;
use Middleware;

class RateLimitMiddleware extends Middleware
{
    private static string $storageFile = __DIR__ . '/../../storage/rate_limits.json';

    public function handle(Request $request, callable $next)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $currentTime = time();

        // Load existing requests from file
        $requests = $this->loadRequests();

        // Simple rate limiting: 100 requests per minute per IP
        if (!isset($requests[$ip])) {
            $requests[$ip] = [];
        }

        // Remove old requests (older than 60 seconds)
        $requests[$ip] = array_filter($requests[$ip], function ($time) use ($currentTime) {
            return ($currentTime - $time) < 60;
        });

        if (count($requests[$ip]) >= 100) {
            new Response()->error('Rate limit exceeded', 429);
            return;
        }

        // Add current request
        $requests[$ip][] = $currentTime;

        // Save back to file
        $this->saveRequests($requests);

        // Continue
        return $next($request);
    }

    private function loadRequests(): array
    {
        if (!file_exists(self::$storageFile)) {
            return [];
        }
        $data = file_get_contents(self::$storageFile);
        return json_decode($data, true) ?: [];
    }

    private function saveRequests(array $requests): void
    {
        $dir = dirname(self::$storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents(self::$storageFile, json_encode($requests));
    }
}
