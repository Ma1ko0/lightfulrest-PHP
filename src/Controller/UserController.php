<?php

namespace App;

use App\User\UserRepository;
use Methods;

class UserController extends Controller
{
	public function __construct(string|Methods $method, array $uriParts) {
		parent::__construct($method, $uriParts);
	}

	public function processRequest(): void {
		$method = $this->getMethod();
		
		if ($method === Methods::GET) {
			switch (strtolower($this->getFirstUriPart())) {
				case "data":
					$this->shiftUriParts();
					if ($this->getUriSize() !== 1) {
						Response::error("User ID is required", 400);
					}
					$userrepo = new UserRepository();
					$userId = $this->getFirstUriPart();
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
		if ($method === Methods::POST) {
		}
		Response::error("Method not allowed", 405);
	}
}
