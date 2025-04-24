<?php

namespace App;

use App\User\UserRepository;

class InputController extends Controller
{
	public function __construct(string $method, array $uriParts) {
		parent::__construct($method, $uriParts);
	}

	public function processRequest(): void {
		$method = $this->getMethod();
		
		if ($method === "GET") {
			switch (strtolower($this->getUriParts()[0])) {
				case "users":
					$this->shiftUriParts();
					if (empty($this->getUriParts())) {
						Response::error("User ID is required", 400);
					}
					$userrepo = new UserRepository();
					$userId = $this->getUriParts()[0];
					$user = $userrepo->getUserById($userId);
					if (empty($user)) {
						Logger::logging("User not found", ERROR);
						Response::error("User not found", 404);
					}
					Logger::logging("User found", INFO);
					Response::json($user->getUsername(), 200);
				default:
					break;
			}
			
		}
		if ($method === "POST") {
		}
		Response::error("Method not allowed", 405);
	}
}
