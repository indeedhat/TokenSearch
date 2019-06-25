<?php

namespace IndeedHat\TokenSearch\Database;

interface DiscoveryAdapterInterface
{
    public function query(string $query): bool;
    public function fetchRow(): ?array;
}
