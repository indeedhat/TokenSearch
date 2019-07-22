<?php

namespace IndeedHat\TokenSearch\Database;

use PDO;
use PDOStatement;

class MySQLDiscoveryAdapter implements DiscoveryAdapterInterface
{
    /**
     * @var Database
     */
    private $pdo;

    /**
     * @var PDOStatement
     */
    private $statement;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $passwd;

    public function __construct(string $host, string $database, string $user, string $passwd)
    {
        $this->pdo = new Database("mysql:host={$host};dbname={$database}", $user, $passwd);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->host     = $host;
        $this->database = $database;
        $this->user     = $user;
        $this->passwd   = $passwd;
    }

    public function query(string $query): bool
    {
        $this->statement = $this->pdo->query($query);

        return $this->statement instanceof PDOStatement && Helper::ok($this->statement);
    }

    public function fetchRow(): ?array
    {
        return $this->statement->fetch() ?: null;
    }
}
