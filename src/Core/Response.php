<?php

declare(strict_types=1);

namespace App;

class Response
{
	/**
	 * Sends CORS (Cross-Origin Resource Sharing) headers to the client.
	 *
	 * This method sets the following headers:
	 * - Access-Control-Allow-Origin: Allows all origins ("*").
	 * - Access-Control-Allow-Methods: Allows GET, POST, and OPTIONS HTTP methods.
	 * - Access-Control-Allow-Headers: Allows headers specified in the incoming request's
	 *   'Access-Control-Request-Headers', or all headers ("*") if not specified.
	 *
	 * @return void
	 */
	private static function CorsHeader() {
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
		header("Access-Control-Allow-Headers: " . ($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? '*'));
	}

	/**
	 * Sends an empty HTTP response with the specified status code.
	 *
	 * This method sets the HTTP response code to the provided status code and then terminates
	 * script execution. No response body is sent.
	 *
	 * @param int $statusCode The HTTP status code to send with the response. Defaults to 200.
	 * @return void
	 */
	public static function empty(int $statusCode = 200): void
	{
		Response::CorsHeader();
		http_response_code($statusCode);
		exit;
	}
	/**
	 * Send a JSON response with the given data and status code.
	 *
	 * @param mixed $data The data to be sent in the response.
	 * @param integer $statusCode The HTTP status code to be sent with the response.
	 * @return void
	 */
	public static function json(mixed $data, int $statusCode = 200): void
	{
		Response::CorsHeader();
		header("Content-Type: application/json; charset=UTF-8");
		http_response_code($statusCode);
		echo json_encode($data);
		exit;
	}
	/**
	 * Send a Text/HTML response with the given data and status code.
	 *
	 * @param mixed $data The data to be sent in the response.
	 * @param integer $statusCode The HTTP status code to be sent with the response.
	 * @return void
	 */
	public static function text(mixed $data, int $statusCode = 200): void
	{
		Response::CorsHeader();
		header("Content-Type: text/html; charset=UTF-8");
		http_response_code($statusCode);
		echo ($data);
		exit;
	}
	/**
	 * Send a redirect response to the specified URL.
	 *
	 * @param string $url The URL to redirect to.
	 * @param integer $statusCode The HTTP status code for the redirect (301 for permanent, 302 for temporary).
	 * @return void
	 */
	public static function redirect(string $url, int $statusCode = 302): void
	{
		Response::CorsHeader();
		http_response_code($statusCode);
		header("Location: $url");
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
		Response::CorsHeader();
		Logger::logging($message, ERROR);
		self::json(["error" => $message], $statusCode);
		exit;
	}
}

