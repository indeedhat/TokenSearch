<?php

namespace IndeedHat\TokenSearch\Database;

use Closure;
use IndeedHat\TokenSearch\Indexer\RowIndexer;
use PDO;
use PDOStatement;

class MySQLStorageAdapter implements StorageAdapterInterface
{
    /**
     * @var Database
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


    public function __construct(string $host, string $database, string $user, string $passwd)
    {
        $this->pdo = new Database("mysql:host={$host};dbname={$database}", $user, $passwd);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->host     = $host;
        $this->database = $database;
        $this->user     = $user;
        $this->passwd   = $passwd;
    }


    public function schemaExists(string $key): bool
    {
        $statement = $this->pdo->query("SHOW TABLES;");
        if (!$statement) {
            return false;
        }

        $fields = [
            "tksearch_word_{$key}"  => false,
            "tksearch_dword_{$key}" => false,
            "tksearch_fword_{$key}" => false,
            "tksearch_field_{$key}" => false,
        ];

        foreach ($statement->fetchAll() as $row) {
            $fields[$row["Tables_in_{$this->database}"]] = true;
        }

        return $fields["tksearch_word_{$key}"]
            && $fields["tksearch_fword_{$key}"]
            && $fields["tksearch_dword_{$key}"]
            && $fields["tksearch_field_{$key}"];
    }

    public function createSchema(string $key): bool
    {
        return (bool)$this->pdo->query("
            CREATE TABLE IF NOT EXISTS tksearch_word_{$key} (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `word` VARCHAR(50),
                `count` INT(11),
                PRIMARY KEY(`id`),
                UNIQUE KEY(`word`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;
        
            CREATE TABLE IF NOT EXISTS tksearch_field_{$key} (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `field` VARCHAR(50),
                PRIMARY KEY(`id`),
                UNIQUE KEY(`field`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS tksearch_dword_{$key} (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `doc_id` INT(11),
                `word_id` INT(11),
                `count` INT(11),
                PRIMARY KEY(`id`),
                UNIQUE KEY(`doc_id`, `word_id`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS tksearch_fword_{$key} (
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

    public function dropSchema(string $key): bool
    {
        $stmt = $this->query("
            DROP TABLE tksearch_word_{$key};
            DROP TABLE tksearch_dword_{$key};
            DROP TABLE tksearch_fword_{$key};
            DROP TABLE tksearch_field_{$key};
        ");

        return Helper::ok($stmt);
    }

    public function insertRow(string $key, RowIndexer $indexer): bool
    {
        // check for existing
        if ($this->docExists($key, $indexer)) {
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
        $this->transaction(function () use ($key, $indexer, $wordRows) {
            foreach ($wordRows as $row) {
                $stmnt = $this->query(
                    "INSERT INTO tksearch_dword_{$key} (doc_id, word_id, count) VALUE (:doc, :word, :count)",
                    ["doc" => $indexer->id, "word" => $row["id"], "count" => $indexer->words[$row["word"]]]
                );

                if (!Helper::ok($stmnt)) {
                    return false;
                }
            }

            return true;
        });

        $fieldRows = $this->loadFields($indexer, $key);

        // insert field words
        return $this->transaction(function () use ($key, $indexer, $fieldRows, $wordRows) {
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


                    if (!Helper::ok($stmt)) {
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
        $stmt = $this->query(
            "SELECT * FROM tksearch_dword_{$key} WHERE doc_id = :id",
            compact("id")
        );
        if (!Helper::ok($stmt)) {
            return false;
        }
        $words = $stmt->fetchAll();

        // delete field words
        $stmt = $this->query(
            "DELETE FROM tksearch_fword_{$key} WHERE doc_id = :id",
            compact("id")
        );
        if (!Helper::ok($stmt)) {
            return false;
        }

        // delete doc words
        $stmt = $this->query(
            "DELETE FROM tksearch_dword_{$key} WHERE doc_id = :id",
            compact("id")
        );
        if (!Helper::ok($stmt)) {
            return false;
        }

        // update word counts
        $outcome = $this->transaction(function () use ($key, $words) {
            foreach ($words as $word) {
                $stmt = $this->query("UPDATE tksearch_word_{$key} SET count = `count` - ?", [$word["count"]]);
                if (!Helper::ok($stmt)) {
                    return false;
                }
            }

            return true;
        });
        if (!$outcome) {
            return false;
        }

        // clear words without counts
        $stmt = $this->query("DELETE FROM tksearch_word_{$key} WHERE count = 0");
        return Helper::ok($stmt);
    }

    public function countDocs(string $key): int
    {
        $statement = $this->query("SELECT COUNT(*) FROM tksearch_doc_{$key};");

        return $statement->fetchColumn(0);
    }

    public function docsForToken(string $key, string $token): array
    {
        $statement = $this->query(
            "SELECT tksearch_dword_{$key}.*, tksearch_word_{$key}.word 
            FROM tksearch_dword_{$key}
            INNER JOIN tksearch_word_{$key} 
            ON tksearch_dword_{$key}.word_id = tksearch_word_{$key}.id
            WHERE word LIKE ?",
            [$token]
        );

        return $statement->fetchAll();
    }

    public function fieldsForToken(string $key, string $token): array
    {
        $statement = $this->query(
            "SELECT tksearch_fword_{$key}.*, tksearch_field_{$key}.field, tksearch_word_{$key}.word
            FROM tksearch_word_{$key}
            INNER JOIN tksearch_fword_{$key} 
            ON tksearch_fword_{$key}.word_id = tksearch_word_{$key}.id
            INNER JOIN tksearch_field_{$key}
            ON tksearch_field_{$key}.id = tksearch_fword_{$key}.field_id
            WHERE word LIKE ?",
            [$token]
        );

        return $statement->fetchAll();
    }

    public function docsForPartialToken(string $key, string $token): array
    {
        return $this->docsForToken($key, "%${token}%");
    }

    public function fieldsForPartialToken(string $key, string $token): array
    {
        return $this->fieldsForToken($key, "%${token}%");
    }

    private function multiLike($field, $words): string
    {
        return implode(" OR ", array_map(function ($word) use ($field) {
            return "`${field}` LIKE ?";
        }), $words);
    }

    private function transaction($callback): bool
    {
        try {
            // stop existing transaction from fucking things up
            $this->pdo->beginTransaction();
        } catch (\Exception $e) {
        }

        $bound   = Closure::bind($callback, $this);
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
        return $this->transaction(function () use ($key, $indexer) {
            foreach ($indexer->fields as $field => $_) {
                $out = $this->query(
                    "INSERT INTO tksearch_field_{$key} (field) VALUE (:field)",
                    compact("field")
                );

                if (!Helper::ok($out) && !Helper::duplicateKey($out)) {
                    return false;
                }
            }

            return true;
        });
    }

    private function insertWords(string $key, RowIndexer $indexer): bool
    {
        return $this->transaction(function () use ($key, $indexer) {
            foreach ($indexer->words as $word => $count) {
                $out = $this->query(
                    "INSERT INTO tksearch_word_{$key} (word, count) VALUE (:word, :count)
                    ON DUPLICATE KEY UPDATE count = `count` + :count",
                    compact("word", "count")
                );

                if (!Helper::ok($out)) {
                    return false;
                }
            }

            return true;
        });
    }

    private function loadFields(RowIndexer $indexer, string $key): array
    {
        $fields    = array_keys($indexer->fields);
        $fieldRows = $this->query("SELECT * FROM tksearch_field_{$key} WHERE `field` IN({$this->in($fields)})", $fields)
            ->fetchAll();

        return $fieldRows;
    }

    private function loadWords(RowIndexer $indexer, string $key): array
    {
        $words    = array_keys($indexer->words);
        $wordRows = $this->query("SELECT * FROM tksearch_word_{$key} WHERE `word` IN({$this->in($words)})", $words)
            ->fetchAll();
        return $wordRows;
    }
}
