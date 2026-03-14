<?php

declare(strict_types=1);

namespace App;

use App\User\UserNotFoundException;
use App\User\UserRepository;
use Methods;

class UserController extends Controller
{
    public function getUserDataByID(string $userId)
    {
        $userrepo = new UserRepository($this->pdo);
        $user = null;
        try {
            $user = $userrepo->getUserById($userId);
        } catch (UserNotFoundException) {
            $this->logger->logging("User not found", ERROR);
            $this->response->error("User not found", 404);
        }
        $this->logger->logging("User found", INFO);
        $this->response->success($user, 200);
    }
}
