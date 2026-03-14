<?php

declare(strict_types=1);

require_once __DIR__ . "/../src/Core/ErrorHandler.php";
require_once __DIR__ . "/../src/Core/HttpException.php";
require_once __DIR__ . "/../src/Core/Logger.php";
use App\ErrorHandler;
use App\HttpException;
use PHPUnit\Framework\TestCase;

class ErrorHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        // Ensure environment is predictable
        ErrorHandler::setDebug(false);
    }

    public function testHandleHttpExceptionOutputsJson(): void
    {
        $exception = new HttpException('Not found', 404);

        ob_start();
        ErrorHandler::handleUncaughtExceptions($exception);
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['status']);
        $this->assertSame('Not found', $decoded['message']);
    }

    public function testHandleUnknownExceptionDoesNotThrow(): void
    {
        $exception = new RuntimeException('Something broke');

        ob_start();
        ErrorHandler::handleUncaughtExceptions($exception);
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertSame('error', $decoded['status']);
        $this->assertArrayHasKey('timestamp', $decoded);
    }
}
