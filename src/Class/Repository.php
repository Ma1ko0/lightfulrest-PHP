<?php

declare(strict_types=1);

class Repository
{
    // Declare Table Names as Constants
    protected const TABLENAME_USER = "Users";
    protected PDO $connection;
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection(): ?PDO
    {
        return $this->connection;
    }
}
