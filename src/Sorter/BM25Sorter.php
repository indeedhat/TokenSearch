<?php

namespace IndeedHat\TokenSearch\Sorter;

use IndeedHat\TokenSearch\Database\StorageAdapterInterface;
use IndeedHat\TokenSearch\ResultsCollection;

class BM25Sorter implements SorterInterface
{
    public function run(StorageAdapterInterface $storage, string $key, array $tokens, array $fiels = []): ResultsCollection
    {
    }
}
