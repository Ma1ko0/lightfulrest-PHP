<?php

namespace App\Middleware;


class AuthMiddleware
{

	public static function checkAuth(): ?bool
	{
		// Check if the user is authenticated
		// This is a simple example, you might want to check a session or a token instead
		if (isset($_SESSION["user"])) {
			return true;
		} else {
			http_response_code(401);
			header("Content-type: application/json; charset=UTF-8");
			echo json_encode(["error" => "Unauthorized"]);
			exit;
		}
	}
}