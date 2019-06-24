<?php

namespace IndeedHat\TokenSearch\Database;

use IndeedHat\TokenSearch\Indexer\RowIndexer;

interface StorageDriverInterface
{
    public function schemaExists(string $key): bool;
    public function createSchema(string $key): bool;

    public function insertRow(string $key, RowIndexer $indexer): bool;
    public function updateRow(string $key, RowIndexer $indexer): bool;
    public function removeRow(string $key, int $id): bool;

    public function query(string $key, array $tokens): array;
}
