<?php

namespace App;

use App\User\UserNotFoundException;
use App\User\UserRepository;
use Methods;

class UserController extends Controller
{
	public function getUserDataByID($userId) {
		$userrepo = new UserRepository();
		$user = null;
		try {
			$user = $userrepo->getUserById($userId);
		} catch (UserNotFoundException) {
			Logger::logging("User not found", ERROR);
			Response::error("User not found", 404);
		}
		Logger::logging("User found", INFO);
		Response::json($user->getUsername(), 200);
	}
}
