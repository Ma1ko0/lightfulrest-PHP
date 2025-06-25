<?php

namespace App;

use Throwable;

class ErrorHandler
{

	private static bool $debug = false;

	public static function handleError(
		int $errno,
		string $errstr,
		string $errfile,
		int $errline
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
			$contextFile
		);

		// Decide based on severity
		switch ($errno) {
			case E_USER_ERROR:
			case E_ERROR:
				Logger::logging($logMessage, ERROR);
				self::outputIfNotProd("ERROR", $logMessage);
				exit(1);

			case E_USER_WARNING:
			case E_WARNING:
				Logger::logging($logMessage, WARN);
				self::outputIfNotProd("WARNING", $logMessage);
				break;

			case E_USER_NOTICE:
			case E_NOTICE:
				Logger::logging($logMessage, INFO);
				self::outputIfNotProd("NOTICE", $logMessage);
				break;

			default:
				Logger::logging("[$errno] $errstr - Unknown error type", WARN);
				self::outputIfNotProd("UNKNOWN", $logMessage);
		}

		http_response_code(500);
		return true;
	}

	public static function handleUncaughtExceptions(Throwable $exception)
	{
		Logger::logging("An uncaught " . get_class($exception) . " was found! " . $exception->getCode() . ": " . $exception->getMessage(), ERROR);
		self::outputIfNotProd("ERROR", $exception->getMessage());
		Response::error("Unknown Exception occurred! Exception Type: " . $exception::class, 500);
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
