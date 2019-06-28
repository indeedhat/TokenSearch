<?php

namespace IndeedHat\TokenSearch\Sorter;

use IndeedHat\TokenSearch\Database\StorageAdapterInterface;
use IndeedHat\TokenSearch\ResultsCollection;

interface SorterInterface
{
    public function run(StorageAdapterInterface $storage, array $tokens, array $fields = []): ResultsCollection;
}
