<?php

declare(strict_types=1);

namespace App;

class Response
{
	/**
	 * Send a JSON response with the given data and status code.
	 *
	 * @param mixed $data The data to be sent in the response.
	 * @param integer $statusCode The HTTP status code to be sent with the response.
	 * @return void
	 */
	public static function json(mixed $data, int $statusCode = 200): void
	{
		header("Content-Type: application/json; charset=UTF-8");
		http_response_code($statusCode);
		echo json_encode($data);
		exit;
	}
	/**
	 * Send a JSON error response with the given message and status code.
	 *
	 * @param string $message The error message to be sent in the response.
	 * @param integer $statusCode The HTTP status code to be sent with the response.
	 * @return void
	 */
	public static function error(string $message, int $statusCode = 400): void
	{
		Logger::logging($message, ERROR);
		self::json(["error" => $message], $statusCode);
		exit;
	}
}

