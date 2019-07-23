<?php

namespace IndeedHat\TokenSearch;

use IndeedHat\TokenSearch\Database\DiscoveryAdapterInterface;
use IndeedHat\TokenSearch\Database\StorageAdapterInterface;
use IndeedHat\TokenSearch\Indexer\RowIndexer;
use IndeedHat\TokenSearch\Sorter\BM25Sorter;
use IndeedHat\TokenSearch\Sorter\SorterInterface;
use IndeedHat\TokenSearch\Tokenizer\TokenizerInterface;
use IndeedHat\TokenSearch\Tokenizer\WhiteSpaceTokenizer;

class TokenSearch
{
    /**
     * @var DiscoveryAdapterInterface
     */
    private $discoveryAdapter;

    /**
     * @var StorageAdapterInterface
     */
    private $storageAdapter;
    
    /**
     * @var TokenizerInterface
     */
    private $tokenizer;

    /**
     * @var SorterInterface
     */
    private $sorter;

    /**
     * @var string
     */
    private $key;

    public function __construct(string $key, ?StorageAdapterInterface $adapter = null)
    {
        $this->key = $key;

        if ($adapter instanceof StorageAdapterInterface) {
            $this->storageAdapter = $adapter;
        }

        $this->initDefaults();
    }

    public function withDiscoveryAdapter(DiscoveryAdapterInterface $adapter)
    {
        $this->discoveryAdapter = $adapter;

        return $this;
    }

    public function withStorageAdapter(StorageAdapterInterface $adapter)
    {
        $this->storageAdapter = $adapter;

        return $this;
    }

    public function withTokenizer(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;

        return $this;
    }

    public function withSorter(SorterInterface $sorter)
    {
        $this->sorter = $sorter;

        return $this;
    }

    public function createIndex(string $query, string $idField): bool
    {
        if (!$this->discoveryAdapter) {
            return false;
        }

        if ($this->storageAdapter->schemaExists($this->key)) {
            return false;
        }

        if (!$this->storageAdapter->createSchema($this->key)) {
            return false;
        }

        if (!$this->discoveryAdapter->query($query)) {
            return false;
        }

        while ($row = $this->discoveryAdapter->fetchRow()) {
            if (empty($row[$idField])) {
                return false;
            }

            $id = $row[$idField];
            unset($row[$idField]);

            $indexer = new RowIndexer($row, $id);
            $indexer->withTokenizer($this->tokenizer);
            $indexer->index();
            if (!$this->storageAdapter->insertRow($this->key, $indexer)) {
                return false;
            }
        }

        return true;
    }

    public function removeIndex(): bool
    {
        return $this->storageAdapter->dropSchema($this->key);
    }

    public function indexExists(): bool
    {
        return $this->storageAdapter->schemaExists($this->key);
    }

    public function updateIndex(string $query, string $idField): bool
    {
        if ($this->indexExists()) {
            if (!$this->removeIndex()) {
                return false;
            }
        }

        return $this->createIndex($query, $idField);
    }

    public function updateDocument(string $query, $idField): bool
    {
        if (!$this->discoveryAdapter) {
            return false;
        }

        if (!$this->storageAdapter->schemaExists($this->key)) {
            return false;
        }

        if (!$this->discoveryAdapter->query($query)) {
            return false;
        }

        $row = $this->discoveryAdapter->fetchRow();
        if (!$row) {
            return false;
        }


        if (empty($row[$idField])) {
            return false;
        }

        $id = $row[$idField];
        unset($row[$idField]);

        $indexer = new RowIndexer($row, $id);
        $indexer->withTokenizer($this->tokenizer);
        $indexer->index();

        return $this->storageAdapter->updateRow($this->key, $indexer);
    }

    public function removeDocument(int $index): bool
    {
        if (!$this->storageAdapter->schemaExists()) {
            return false;
        }

        return $this->storageAdapter->removeRow($index);
    }

    public function search(string $query, array $fields = []): ResultsCollection
    {
        $tokens = $this->tokenizer->tokenize($query);
        return $this->sorter->run($this->storageAdapter, $this->key, $tokens, $fields);
    }

    protected function initDefaults(): void
    {
        if (!$this->tokenizer) {
            $this->tokenizer = new WhiteSpaceTokenizer();
        }

        if (!$this->sorter) {
            $this->sorter = new BM25Sorter();
        }
    }
}
