<?php

declare(strict_types=1);

namespace App\User;

class User
{
	private int $id;

	private string $username;

	private string $email;

	private string $password_hash;

	private string $createdAt;

	private string $updatedAt;


	public function __construct(int $id, string $username, string $email, string $password_hash, ?string $createdAt, ?string $updatedAt)
	{
		$this->id = $id;
		if (empty($id)) {
			throw new \InvalidArgumentException("ID cannot be empty");
		}
		$this->username = strtolower($username);
		if (empty($this->username)) {
			throw new \InvalidArgumentException("Username cannot be empty");
		}
		$this->email = strtolower($email);
		if (empty($this->username)) {
			throw new \InvalidArgumentException("Username cannot be empty");
		}
		$this->password_hash = $password_hash;
		if (empty($this->password_hash)) {
			throw new \InvalidArgumentException("Password cannot be empty");
		}
		$this->createdAt = $createdAt;
		$this->updatedAt = $updatedAt;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUsername(): string
	{
		return $this->username;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function getPasswordHash(): string
	{
		return $this->password_hash;
	}

	public function getCreatedAt(): string
	{
		return $this->createdAt;
	}

	public function getUpdatedAt(): string
	{
		return $this->updatedAt;
	}
}
