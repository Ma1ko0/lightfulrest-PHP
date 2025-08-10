<?php

namespace App;

use App\User\UserRepository;
use Methods;

class RoutingController extends Controller
{
	public function __construct(string|Methods $method, array $uriParts)
	{
		parent::__construct($method, $uriParts);
	}
	public function processRequest(): void {
		$controller = null;
		switch (strtolower($this->getFirstUriPart())) {
			case "users":
				$this->shiftUriParts();
				$controller = new UserController($this->getMethod(), $this->getUriParts());
				break;
			default:
				Response::error("Not Found!", 404);
					break;
		}
		$controller->processRequest();
	}
}
