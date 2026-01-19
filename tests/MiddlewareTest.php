<?php

include_once __DIR__ . "/../src/Middleware/CorsMiddleware.php";
include_once __DIR__ . "/../src/Middleware/JsonContentTypeMiddleware.php";
include_once __DIR__ . "/../src/Middleware/RateLimitMiddleware.php";
include_once __DIR__ . "/../src/Middleware/LoggingMiddleware.php";
include_once __DIR__ . "/../src/Middleware/SessionMiddleware.php";
include_once __DIR__ . "/../src/Middleware/RestAuthMiddleware.php";
include_once __DIR__ . "/../src/Core/Request.php";
require_once __DIR__ . "/../src/Core/Request.php";
require_once __DIR__ . "/../src/Core/Logger.php";
require_once __DIR__ . "/../src/Enums/Methods.php";
require_once __DIR__ . "/Fakes/ResponseFake.php";
require_once __DIR__ . "/../src/Class/Repository.php";
require_once __DIR__ . "/../src/Models/User/UserRepository.php";


use PHPUnit\Framework\TestCase;
use App\Middleware\CorsMiddleware;
use App\Middleware\JsonContentTypeMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\LoggingMiddleware;
use App\Middleware\SessionMiddleware;
use App\Middleware\RestAuthMiddleware;
use App\Request;

class MiddlewareTest extends TestCase
{
    public function testCorsMiddlewareSetsHeaders()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $request = new Request();

        ob_start();
        CorsMiddleware::handle($request, function($req) {
            echo 'next';
        });
        $output = ob_get_clean();

        $this->assertEquals('next', $output);
    }

    public function testJsonContentTypeMiddlewareAllowsValid()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $request = new Request();

        ob_start();
        JsonContentTypeMiddleware::handle($request, function($req) {
            echo 'ok';
        });
        $output = ob_get_clean();

        $this->assertEquals('ok', $output);
    }

    public function testJsonContentTypeMiddlewareRejectsInvalid()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Content-Type must be application/json');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['CONTENT_TYPE'] = 'text/plain';
        $request = new Request();

        JsonContentTypeMiddleware::handle($request, function($req) {
            // Should not reach here
        });
    }

    public function testRateLimitMiddlewareAllowsUnderLimit()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $request = new Request();

        ob_start();
        RateLimitMiddleware::handle($request, function($req) {
            echo 'allowed';
        });
        $output = ob_get_clean();

        $this->assertEquals('allowed', $output);
    }

    public function testLoggingMiddlewareLogsRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $request = new Request();

        ob_start();
        LoggingMiddleware::handle($request, function($req) {
            echo 'logged';
        });
        $output = ob_get_clean();

        $this->assertEquals('logged', $output);
        // Note: In a real test, you'd mock Logger to verify logging
    }

    public function testSessionMiddlewareStartsSession()
    {
        // Reset session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $request = new Request();

        SessionMiddleware::handle($request, function($req) {
            return 'session started';
        });

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
    }

    public function testRestAuthMiddlewareValidatesToken()
    {
        // Set up JWT environment variables for testing
        $_ENV['JWT_SECRET'] = 'test-jwt-secret-key-that-is-long-enough-for-hs256-algorithm-32-bytes-minimum';
        $_ENV['JWT_ALGORITHM'] = 'HS256';
        
        // Generate a valid JWT token for testing (without user_id to skip user validation)
        $payload = ['username' => 'testuser', 'role' => 'admin'];
        $token = RestAuthMiddleware::generateToken($payload, 1); // 1 hour expiration

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        $request = new Request();

        ob_start();
        RestAuthMiddleware::handle($request, function($req) {
            echo 'authenticated';
        });
        $output = ob_get_clean();

        $this->assertEquals('authenticated', $output);
    }

    public function testRestAuthMiddlewareRejectsNoToken()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Authorization header required');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        unset($_SERVER['HTTP_AUTHORIZATION']);
        $request = new Request();

        RestAuthMiddleware::handle($request, function($req) {
            // Should not reach
        });
    }
}