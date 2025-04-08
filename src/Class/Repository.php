<?php

use App\Response;

class Repository
{
	// Declare Table Names as Constants
	// This is a good practice to avoid typos in table names and to make it easier to change them in the future.
	protected const TABLENAME_USER = "Users";

	// Declare some other contants or functions that are used in the repository class
	protected static $dbHost;
	protected static $dbName;
	protected static $dbUser;
	protected static $dbPassword;

	public static function init(): void
	{
		// Setze die Umgebungsvariablen
		self::$dbHost = getenv('DB_HOST') ?: 'localhost';  // Fallback auf 'localhost', wenn nicht gesetzt
		self::$dbName = getenv('DB_NAME') ?: 'mydatabase';
		self::$dbUser = getenv('DB_USER') ?: 'root';
		self::$dbPassword = getenv('DB_PASSWORD') ?: '12345678';
	}

	public static function getConnection(): ?PDO
	{
		// Stelle sicher, dass init() vorher aufgerufen wurde
		if (!isset(self::$dbHost)) {
			self::init();
		}

		try {
			$dsn = "mysql:host=" . self::$dbHost . ";dbname=" . self::$dbName;
			$pdo = new PDO($dsn, self::$dbUser, self::$dbPassword);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $pdo;
		} catch (PDOException $e) {
			Response::error("Database connection failed: " . $e->getMessage(), 500);
			return null;
		}
	}
}
