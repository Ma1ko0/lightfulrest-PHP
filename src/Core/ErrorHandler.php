<?php

declare(strict_types=1);

namespace App;

use DateTime;
use Throwable;

class ErrorHandler
{
    private static bool $debug = false;

    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline,
    ): bool {
        // Skip if error reporting doesn't include this error type
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // Try to get deeper stack trace info (optional)
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $contextFile = $trace[1]['file'] ?? $errfile;
        $contextLine = $trace[1]['line'] ?? $errline;

        // Prepare log message
        $logMessage = sprintf(
            "[%d] %s (Line %d in File %s)",
            $errno,
            $errstr,
            $contextLine,
            $contextFile,
        );
        $logger = new Logger();
        // Decide based on severity
        switch ($errno) {
            case E_USER_ERROR:
            case E_ERROR:
                $logger->logging($logMessage, ERROR);
                self::outputIfNotProd("ERROR", $logMessage);
                http_response_code(500);
                exit(1);

            case E_USER_WARNING:
            case E_WARNING:
                $logger->logging($logMessage, WARN);
                self::outputIfNotProd("WARNING", $logMessage);
                break;

            case E_USER_NOTICE:
            case E_NOTICE:
                $logger->logging($logMessage, INFO);
                self::outputIfNotProd("NOTICE", $logMessage);
                break;

            default:
                $logger->logging("[$errno] $errstr - Unknown error type", WARN);
                self::outputIfNotProd("UNKNOWN", $logMessage);
        }

        // http_response_code(500);
        return true;
    }
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error === null) {
            return;
        }

        // Only handle fatal shutdown errors
        if (in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
            $message = sprintf("Shutdown due to fatal error: %s in %s on line %d", $error['message'], $error['file'], $error['line']);
            (new Logger())->logging($message, ERROR);

            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'A fatal error occurred while processing the request.',
                'details' => self::$debug ? $message : null,
                'timestamp' => date(DateTime::ATOM),
            ]);
        }
    }

    public static function handleUncaughtExceptions(Throwable $exception)
    {
        new Logger()->logging("An uncaught " . get_class($exception) . " was found! " . $exception->getCode() . ": " . $exception->getMessage(), ERROR);

        if ($exception instanceof HttpException) {
            http_response_code($exception->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'timestamp' => date(DateTime::ATOM),
            ]);
            return;
        }

        self::outputIfNotProd("ERROR", $exception->getMessage());

        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Unknown Exception occurred! Exception Type: ' . $exception::class,
            'details' => self::$debug ? $exception->getMessage() : null,
            'timestamp' => date(DateTime::ATOM),
        ]);
    }

    private static function outputIfNotProd(string $level, string $message): void
    {
        if (self::$debug) {
            echo sprintf("%s: %s\n", $level, $message);
        }
    }

    public static function setDebug(bool $debug)
    {
        self::$debug = $debug;
    }
}
