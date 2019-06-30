<?php

namespace IndeedHat\TokenSearch\Sorter;

use IndeedHat\TokenSearch\Database\StorageAdapterInterface;
use IndeedHat\TokenSearch\ResultsCollection;

interface SorterInterface
{
    public function run(StorageAdapterInterface $storage, string $key, array $tokens, array $fields = []): ResultsCollection;
}
