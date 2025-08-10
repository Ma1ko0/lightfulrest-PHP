<?php

namespace App;

use App\User\UserRepository;
use Methods;

class UserController extends Controller
{
	public function getUserDataByID($userId) {
		$userrepo = new UserRepository();
		$user = $userrepo->getUserById($userId);
		if (empty($user)) {
			Logger::logging("User not found", ERROR);
			Response::error("User not found", 404);
		}
		Logger::logging("User found", INFO);
		Response::json($user->getUsername(), 200);
	}
}
