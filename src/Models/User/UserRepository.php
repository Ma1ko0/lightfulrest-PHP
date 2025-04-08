<?php

declare(strict_types=1);

namespace App\User;

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
		$query = <<<SQL
			SELECT * FROM users WHERE id = ?
		SQL;
		$stmt = $this->databaseConnection->prepare($query);
		$stmt->execute([$userId]);
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (sizeof($result) != 1) {
			throw new UserNotFoundException();
		}
		return $this->mapRowToUser($result[0]);
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
