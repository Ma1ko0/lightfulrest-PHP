<?php

declare(strict_types=1);

use App\DatabaseConnectionException;
use App\Response;

class Database
{
    // Declare some other constants or functions that are used in the repository class
    private string $dbHost;
    private string $dbName;
    private string $dbUser;
    private string $dbPassword;

    public function __construct()
    {
        // Set Env variables
        $this->dbHost = getenv('DB_HOST') ?: 'localhost';  // Fallback to 'localhost', if not set
        $this->dbName = getenv('DB_NAME') ?: 'mydatabase';
        $this->dbUser = getenv('DB_USER') ?: 'root';
        $this->dbPassword = getenv('DB_PASSWORD') ?: '12345678';
    }

    public function getConnection(): ?PDO
    {

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // Enable persistent connections only when explicitly requested
        if (filter_var(getenv('DB_PERSISTENT') ?: 'false', FILTER_VALIDATE_BOOLEAN)) {
            $options[PDO::ATTR_PERSISTENT] = true;
        }
        try {
            $dsn = "mysql:host=" . $this->dbHost . ";dbname=" . $this->dbName;
            $pdo = new PDO($dsn, $this->dbUser, $this->dbPassword, $options);
            return $pdo;
        } catch (PDOException $e) {
            throw new DatabaseConnectionException('Database connection failed: ' . $e->getMessage(), 500, $e);
        }
    }
}
