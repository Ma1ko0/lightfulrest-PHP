<?php

namespace App\Middleware;

use App\Request;
use App\Response;
use App\User\UserRepository;
use Exception;

class RestAuthMiddleware
{
    public static function handle(Request $request, callable $next)
    {
        // Check for Authorization header
        $authHeader = $request->getHeaders('Authorization');
        if (!$authHeader) {
            Response::error('Authorization header required', 401);
            return;
        }

        // Assuming Bearer token
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            // Validate token (placeholder - implement JWT or API key validation)
            if (!self::validateToken($token)) {
                Response::error('Invalid token', 401);
                return;
            }
        } else {
            Response::error('Invalid Authorization format', 401);
            return;
        }

        // Continue
        return $next($request);
    }

    private static function getAlgorithm(): string
    {
        return $_ENV['JWT_ALGORITHM'] ?? 'HS256';
    }

    private static function validateToken(string $token): bool
    {
        try {
            $algorithm = self::getAlgorithm();
            $key = self::getSecretKey();
            
            if (in_array($algorithm, ['RS256', 'RS384', 'RS512', 'ES256', 'ES384', 'ES512'])) {
                // For asymmetric algorithms, we need the public key
                $key = self::getPublicKey();
            }
            
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($key, $algorithm));

            // Check if token is expired
            if (isset($decoded->exp) && $decoded->exp < time()) {
                return false;
            }

            // Optional: Check if user still exists and is active
            if (isset($decoded->user_id)) {
                return self::isUserValid($decoded->user_id);
            }

            return true;
        } catch (\Firebase\JWT\ExpiredException $e) {
            // Token has expired
            return false;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            // Token signature is invalid
            return false;
        } catch (\Exception $e) {
            // Other validation errors
            return false;
        }
    }

    private static function getSecretKey(): string
    {
        return $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this-in-production';
    }

    private static function isUserValid(int $userId): bool
    {
        $userRepo = new \App\User\UserRepository();
        try {
            $user = $userRepo->getUserById($userId);
            return $user !== null;
        } catch (\App\User\UserNotFoundException $e) {
            return false;
        }
    }

    private static function getPublicKey(): string
    {
        return $_ENV['JWT_PUBLIC_KEY'] ?? '';
    }

    private static function getPrivateKey(): string
    {
        return $_ENV['JWT_PRIVATE_KEY'] ?? self::getSecretKey();
    }

    // Helper method to generate JWT tokens (for login endpoint)
    public static function generateToken(array $payload, int $expirationHours = 24): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + ($expirationHours * 3600);

        $payload = array_merge($payload, [
            'iat' => $issuedAt,        // Issued at
            'exp' => $expirationTime,  // Expiration time
            'iss' => 'lightful-api',   // Issuer
        ]);

        $algorithm = self::getAlgorithm();
        $key = self::getPrivateKey();
        
        return \Firebase\JWT\JWT::encode($payload, $key, $algorithm);
    }
}