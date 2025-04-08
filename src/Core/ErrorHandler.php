<?php

namespace App;

class ErrorHandler
{
	// error handler function
	public static function handleError($errno, $errstr, $errfile, $errline) {
		if (!(error_reporting() & $errno)) {
			return false;
		}

		$bt = debug_backtrace();
		$debug = array_shift($bt);

		if (!isset($debug["line"])) {
			$debug["line"] = $errline;
			$debug["file"] = $errfile;
		}

		switch ($errno) {
			case E_USER_ERROR:
			case E_ERROR:
				$out = "ERROR [$errno] $errstr\n
							Error in File $errfile in Line $errline,
							PHP " . PHP_VERSION . " (" . PHP_OS . ")\n
							";
				Logger::logging("[$errno] $errstr  - Error in File $errfile in Line $errline", ERROR);
				echo $out;
				exit(1);
			case E_USER_WARNING:
			case E_WARNING:
				$out = "WARNING [$errno] $errstr (Line " . $debug["line"] . " in File " . $debug["file"] . ")\n\n";
				Logger::logging("[$errno] $errstr (Line " . $debug["line"] . " in File " . $debug["file"] . ")", WARN);
				break;
			case E_USER_NOTICE:
			case E_NOTICE:
				$out = "NOTICE [$errno] $errstr (Line " . $debug["line"] . " in File " . $debug["file"] . ")\n\n";
				Logger::logging("[$errno] $errstr (Line " . $debug["line"] . " in File " . $debug["file"] . ")", INFO);
				break;
			default:
				$out = "Unknown error type: [$errno] $errstr\n\n";
				Logger::logging("[$errno] $errstr - Unknown error type", WARN);
				break;
		}
		http_response_code(500);
		return true;
	}
}
