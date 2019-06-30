<?php

namespace IndeedHat\TokenSearch\Database;

use IndeedHat\TokenSearch\Indexer\RowIndexer;

interface StorageAdapterInterface
{
    public function schemaExists(string $key): bool;
    public function createSchema(string $key): bool;
    public function dropSchema(string $key): bool;

    public function insertRow(string $key, RowIndexer $indexer): bool;
    public function updateRow(string $key, RowIndexer $indexer): bool;
    public function removeRow(string $key, int $id): bool;

    /* public function findWords(string $key, array $tokens, array $fields = []): array; */
    /* public function findPartialWords(string $key, array $tokens, array $fields = []): array; */

    public function countDocs(string $key): int;
    public function docsForToken(string $key, string $token): array;
    public function fieldsForToken(string $key, string $token): array;
    public function docsForPartialToken(string $key, string $token): array;
    public function fieldsForPartialToken(string $key, string $token): array;
}
