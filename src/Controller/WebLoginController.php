<?php

declare(strict_types=1);

use App\Controller;
use App\Response;
use App\User\UserRepository;

class WebLoginController extends Controller
{
    public function login()
    {
        $email = $this->request->getPostData('email') ?? '';
        $password = $this->request->getPostData('password') ?? '';
        $userRepo = new UserRepository($this->pdo);
        try {
            $user = $userRepo->getUserByEmail($email);
        } catch (\App\User\UserNotFoundException $th) {
            return $this->response->text("Der angegebene Benutzer existiert nicht!");
        }
        if (password_verify($password, $user->getPasswordHash())) {
            if (password_needs_rehash($user->getPasswordHash(), PASSWORD_ARGON2ID)) {
                $newHash = password_hash($password, PASSWORD_ARGON2ID);
                return $userRepo->updatePasswordHash($user, $newHash);
            }
            return $this->response->text("<div class=\"text-success\">Erfolgreich Eingeloggt!<div>");
        } else {
            return $this->response->text("<div class=\"text-danger\">Falsche Anmeldedaten!<div>");
        }
    }
}
