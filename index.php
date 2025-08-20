<?php

declare(strict_types=1);

namespace App;

spl_autoload_register(function ($class) {
	if (str_contains($class, "\\")) {
		$class = substr($class, strrpos($class, "\\") + 1);
	}
	if (is_file(__DIR__  . "/src/$class.php")) {
		require_once __DIR__ . "/src/$class.php";
	}
	if (is_file(__DIR__ . "/src/Class/$class.php")) {
		require_once __DIR__ . "/src/Class/$class.php";
	}
	if (is_file(__DIR__ . "/src/Core/$class.php")) {
		require_once __DIR__ . "/src/Core/$class.php";
	}
	if (is_file(__DIR__ . "/src/Controller/$class.php")) {
		require_once __DIR__ . "/src/Controller/$class.php";
	}
	if (is_file(__DIR__ . "/src/Enums/$class.php")) {
		require_once __DIR__ . "/src/Enums/$class.php";
	}
	$models = array_slice(scandir(__DIR__ . "/src/Models"), 2);
	foreach ($models as $model) {
		if (is_file(__DIR__ . "/src/Models/$model/$class.php")) {
			require_once __DIR__ . "/src/Models/$model/$class.php";
		}
	}
});

define("NONE", 1);
define("ERROR", 2);
define("WARN", 4);
define("INFO", 8);
define("DEBUG", 16);

session_start();
Logger::setLevel(Logger::parseLogLevel($_ENV["LOG_LEVEL"]));
error_reporting(E_ALL);
ErrorHandler::setDebug(filter_var($_ENV["DEBUG_MODE"], FILTER_VALIDATE_BOOLEAN));

set_error_handler("App\\ErrorHandler::handleError");
set_exception_handler("APP\\ErrorHandler::handleUncaughtExceptions");

date_default_timezone_set($_ENV["TIMEZONE"]);
setlocale(LC_ALL, $_ENV["LOCALE"]);

include_once __DIR__ . "/src/Core/Routes.php";