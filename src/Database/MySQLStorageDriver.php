<?php

namespace IndeedHat\TokenSearch\Database;

use IndeedHat\TokenSearch\Indexer\RowIndexer;
use PDO;

class MySQLStorageDriver implements StorageDriverInterface
{
    /**
     * @var PDO
     */
    private $pdo;

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


    function __construct(string $host, string $database, string $user, string $passwd)
    {
        $this->pdo = new PDO("mysql:host={$host};dbname={$database}", $user, $passwd);
        $this->host = $host;
        $this->database = $database;
        $this->user = $user;
        $this->passwd = $passwd;
    }


    public function schemaExists(string $key): bool
    {
        $result = $this->pdo->query(
<<<QUERY_
            SELECT 
                COUNT(*) as c
            FROM
                `information_schema`.`tables`
            WHERE
                `table_schmea` = {$this->pdo->quote($this->database)}
            AND
                `table_name` IN(
                    {$this->pdo->quote("ihat_search_word_{$key}")},
                    {$this->pdo->quote("ihat_search_field_{$key}")},
                    {$this->pdo->quote("ihat_search_dword_{$key}")},
                    {$this->pdo->quote("ihat_search_fword_{$key}")}
                )
QUERY_
        );

        return 4 == $result["c"];
    }

    public function createSchema(string $key): bool
    {
        return (bool)$this->pdo->quey("
            CREATE TABLE ihat_search_word_{$key} (
                `id` INT AUTO_INCREMENT,
                `word` VARCHAR(50),
                `count` INT,
                PRIMARY_KEY(`id`),
                UNIQUE KEY(`word`)
            ) ENGINE=INNODB DEFAULT CHARSET=uft8mb4 COLLATION=utf8mb4_bin;
        
            CREATE TABLE ihat_search_field_{$key} (
                `id` INT AUTO_INCREMENT,
                `field` VARCHAR(50),
                PRIMARY_KEY(`id`),
                UNIQUE KEY(`field`)
            ) ENGINE=INNODB DEFAULT CHARSET=uft8mb4 COLLATION=utf8mb4_bin;

            CREATE TABLE ihat_search_dword_{$key} (
                `id` INT AUTO_INCREMENT,
                `doc_id` INT,
                `word_id` INT,
                `count` INT,
                PRIMARY_KEY(`id`),
                UNIQUE KEY(`doc_id`, `word_id`)
            ) ENGINE=INNODB DEFAULT CHARSET=uft8mb4 COLLATION=utf8mb4_bin;

            CREATE TABLE ihat_search_fword_{$key} (
                `id` INT AUTO_INCREMENT,
                `doc_id` INT,
                `field_id` INT,
                `word_id` INT,
                `count` INT,
                PRIMARY_KEY(`id`),
                UNIQUE KEY(`doc_id`, `field_id`, `word_id`)
            ) ENGINE=INNODB DEFAULT CHARSET=uft8mb4 COLLATION=utf8mb4_bin;
        ");
    }

    public function insertRow(string $key, RowIndexer $indexer): bool
    {
        // check for existing

        // insert all words

        // load all words
        
        // insert doc words

        // insert field words
    }

    public function updateRow(string $key, RowIndexer $indexer): bool
    {
        $this->pdo->beginTransaction();

        if ($this->removeRow($key, $indexer->id) && $this->insertRow($key, $indexer)) {
            $this->pdo->commit();
            return true;
        } 

        $this->pdo->rollBack();
        return false;
    }

    public function removeRow(string $key, int $id): bool
    {
        // load doc words
        
        // delete field words

        // delete doc words

        // update word counts
    }

    public function findWords(string $key, array $tokens, array $fields = []): array
    {
    }

    public function findPartialWords(string $key, array $tokens, array $fields = []): array
    {
    }
}
