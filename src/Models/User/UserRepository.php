<?php

declare(strict_types=1);

namespace App\User;

use App\DatabaseQueryBuilder;
use App\Response;
use PDO;
use Repository;

class UserRepository extends Repository
{
	private PDO $databaseConnection;

	public function __construct()
	{
		$this->databaseConnection = self::getConnection();
	}

	public function getUserById(string $userId): User
	{
		if (empty($userId)) {
			throw new \InvalidArgumentException("User ID cannot be empty");
		}

		if (!is_string($userId)) {
			throw new \InvalidArgumentException("User ID must be a string");
		}

		if (strlen($userId) > 255) {
			throw new \LengthException("User ID is too long");
		}

		if (!preg_match("/^[a-zA-Z0-9]+$/", $userId)) {
			throw new \InvalidArgumentException("User ID contains invalid characters");
		}
		$table = self::TABLENAME_USER;
		$result = (new DatabaseQueryBuilder($this->databaseConnection))
			->select()
			->table($table)
			->where("id", "=", $userId)
			->get();
		if (sizeof($result) != 1) {
			throw new UserNotFoundException();
		}
		return $this->mapRowToUser($result[0]);
	}
	public function getUserByEmail(string $email): User
	{
		if (empty($email)) {
			throw new \InvalidArgumentException("Email cannot be empty");
		}

		if (!is_string($email)) {
			throw new \InvalidArgumentException("Email must be a string");
		}

		if (strlen($email) > 255) {
			throw new \LengthException("Email is too long");
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new \InvalidArgumentException("Email contains invalid characters");
		}
		$table = self::TABLENAME_USER;
		$result = (new DatabaseQueryBuilder($this->databaseConnection))
			->select()
			->table($table)
			->where("email", "=", $email)
			->get();
		if (sizeof($result) != 1) {
			throw new UserNotFoundException();
		}
		return $this->mapRowToUser($result[0]);
	}

	public function updatePasswordHash(User $user, string $hash) {
		if (empty($user) || $user === null) {
			throw new \InvalidArgumentException("User cannot be empty");
		}
		if (empty($hash) || $hash === null) {
			throw new \InvalidArgumentException("Hash cannot be empty");
		}
		if (!is_string($hash)) {
			throw new \InvalidArgumentException("Hash must be a string");
		}

		if (strlen($hash) > 255) {
			throw new \LengthException("Hash is too long");
		}

		$table = self::TABLENAME_USER;
		$result = (new DatabaseQueryBuilder($this->databaseConnection))
			->update(["password_hash" => $hash])
			->table($table)
			->where("id", "=", $user->getId())
			->where("email", "=", $user->getEmail())
			->where("username", "=", $user->getUsername())
			->getSQL();

		return Response::text($result);
	}

	private function mapRowToUser(array $row): User
	{
		return new User(
			$row["id"],
			$row["username"],
			$row["email"],
			$row["password_hash"],
			$row["created_at"],
			$row["updated_at"]
		);
	}
}
