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
ErrorHandler::setDebug(boolval($_ENV["DEBUG_MODE"]));

set_error_handler("App\\ErrorHandler::handleError");
set_exception_handler("APP\\ErrorHandler::handleUncaughtExceptions");

date_default_timezone_set($_ENV["TIMEZONE"]);
setlocale(LC_ALL, $_ENV["LOCALE"]);

$fileName = "/" . basename(__FILE__);
if (strpos($_SERVER["REQUEST_URI"], $fileName) === 0) {
	$requestURI = substr($_SERVER["REQUEST_URI"], strlen($fileName));
} else {
	$requestURI = $_SERVER["REQUEST_URI"];
}
if (strpos($requestURI, "?") !== false) {
	$requestURI = substr($requestURI, 0, strpos($requestURI, "?"));
}
$parts = explode("/", $requestURI);
array_shift($parts);

if ($parts === null) {
	http_response_code(404);
	exit;
}
$controller = new RoutingController($_SERVER["REQUEST_METHOD"], $parts);
$controller->processRequest();
