<?php

namespace IndeedHat\TokenSearch\Database;

use Closure;
use IndeedHat\TokenSearch\Indexer\RowIndexer;
use PDO;
use PDOStatement;

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
                `information_schema`.`TABLES`
            WHERE
                `table_schmea` = {$this->pdo->quote($this->database)}
            AND
                `table_name` IN(
                    {$this->pdo->quote("tksearch_word_{$key}")},
                    {$this->pdo->quote("tksearch_field_{$key}")},
                    {$this->pdo->quote("tksearch_dword_{$key}")},
                    {$this->pdo->quote("tksearch_fword_{$key}")}
                )
QUERY_
        );

        return 4 == $result["c"];
    }

    public function createSchema(string $key): bool
    {
        return (bool)$this->pdo->query("
            CREATE TABLE tksearch_word_{$key} (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `word` VARCHAR(50),
                `count` INT(11),
                PRIMARY KEY(`id`),
                UNIQUE KEY(`word`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;
        
            CREATE TABLE tksearch_field_{$key} (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `field` VARCHAR(50),
                PRIMARY KEY(`id`),
                UNIQUE KEY(`field`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE tksearch_dword_{$key} (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `doc_id` INT(11),
                `word_id` INT(11),
                `count` INT(11),
                PRIMARY KEY(`id`),
                UNIQUE KEY(`doc_id`, `word_id`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE tksearch_fword_{$key} (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `doc_id` INT(11),
                `field_id` INT(11),
                `word_id` INT(11),
                `count` INT(11),
                PRIMARY KEY(`id`),
                UNIQUE KEY(`doc_id`, `field_id`, `word_id`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 ;
        ");
    }

    public function insertRow(string $key, RowIndexer $indexer): bool
    {
        // check for existing
        if($this->docExists($key, $indexer)) {
            return false;
        }

        // insert all fields
        if (!$this->insertFields($key, $indexer)) {
            return false;
        }

        // insert all words
        if (!$this->insertWords($key, $indexer)) {
            return false;
        }

        $wordRows = $this->loadWords($indexer, $key);
        
        // insert doc words
        $this->transaction(function() use ($key, $indexer, $wordRows) { 
            foreach ($wordRows as $row) {
                $stmnt = $this->query(
                    "INSERT INTO tksearch_dword_{$key} (doc_id, word_id, count) VALUE (:doc, :word, :count)",
                    ["doc" => $indexer->id, "word" => $row["id"], "count" => $indexer->words[$row["word"]]]
                );

                if ("00000" != $stmnt->errorCode()) {
                    return false;
                }
            }

            return true;
        });

        $fieldRows = $this->loadFields($indexer, $key);

        // insert field words
        return $this->transaction(function() use ($key, $indexer, $fieldRows, $wordRows) {
            foreach ($fieldRows as $field) {
                $fieldIndex = $indexer->fields[$field["field"]];
                foreach ($wordRows as $word) {
                    if (!isset($fieldIndex->words[$word["word"]])) {
                        continue;
                    }
                    $stmt = $this->query(
                        "INSERT INTO tksearch_fword_{$key} (doc_id, word_id, field_id, count) VALUES (:doc, :word, :field, :count)",
                        ["doc" => $indexer->id, "word" => $word["id"], "field" => $field["id"], "count" => $fieldIndex->words[$word["word"]]]
                    );


                    if ("00000" != $stmt->errorCode()) {
                        return false;
                    }
                }
            }

            return true;
        });
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
        $statement = $this->query(
            "SELECT * FROM tksearch_word_{$key} WHERE {$this->multiLike("word", $tokens)}", 
            $tokens
        );

        return $statement->fetchAll();
    }

    public function findPartialWords(string $key, array $tokens, array $fields = []): array
    {
    }

    private function multiLike($field, $words): string
    {
        return implode(" OR ", array_map(function($word) use ($field) { 
            return "`$field` LIKE ?";
        }), $words);
    }

    private function transaction($callback): bool
    {
        $this->pdo->beginTransaction();

        $bound = Closure::bind($callback, $this);
        $outcome = $bound();

        if ($outcome) {
            $this->pdo->commit();
        } else {
            $this->pdo->rollBack();
        }

        return $outcome;
    }


    private function query(string $query, array $params = []): PDOStatement
    {
        $stmnt = $this->pdo->prepare($query);
        $stmnt->execute($params);
        return $stmnt;
    }

    private function in(array $input): string
    {
        return str_repeat("?,", count($input) - 1) . "?";
    }

    private function docExists(string $key, RowIndexer $indexer): bool
    {
        $stmnt = $this->query(
            "SELECT count(*) as c FROM tksearch_dword_{$key} WHERE doc_id = :id",
            ["id" => $indexer->id]
        );
        $count = $stmnt->fetchColumn(0);
        return (bool)$count;
    }

    private function insertFields(string $key, RowIndexer $indexer): bool
    {
        return $this->transaction(function() use ($key, $indexer) {

            foreach ($indexer->fields as $field => $_) {
                $out = $this->query(
                    "INSERT INTO tksearch_field_{$key} (field) VALUE (:field)",
                    compact("field")
                );

                if ("00000" != $out->errorCode()) {
                    return false;
                }
            }

            return true;
        });
    }

    private function insertWords(string $key, RowIndexer $indexer): bool
    {
        return $this->transaction(function() use ($key, $indexer) {
            foreach ($indexer->words as $word => $count) {
                $out = $this->query(
                    "INSERT INTO tksearch_word_{$key} (word, count) VALUE (:word, :count)
                    ON DUPLICATE KEY UPDATE count = `count` + :count",
                    compact("word", "count")
                );
            
                if ("00000" != $out->errorCode()) {
                    return false;
                }
            }

            return true;
        });
    }

    private function loadFields(RowIndexer $indexer, string $key): array
    {
        $fields = array_keys($indexer->fields);
        $fieldRows = $this->query("SELECT * FROM tksearch_field_{$key} WHERE `field` IN({$this->in($fields)})", $fields)
            ->fetchAll();

        return $fieldRows;
    }

    private function loadWords(RowIndexer $indexer, string $key): array
    {
        $words = array_keys($indexer->words);
        $wordRows = $this->query("SELECT * FROM tksearch_word_{$key} WHERE `word` IN({$this->in($words)})", $words)
            ->fetchAll();
        return $wordRows;
    }
}

