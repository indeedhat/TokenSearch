<?php

namespace IndeedHat\TokenSearch\Database;

interface DiscoveryDriverInterface
{
    public function query(string $quert): array;
}
