<?php

namespace IndeedHat\TokenSearch;

/**
 * @property int $docId
 * @property fload $weight
 */
class Result
{
    /**
     * @var int
     */
    private $docId;

    /**
     * @var float
     */
    private $weight;

    function __construct(int $docId, float $weight)
    {
        $this->docId = $docId;
        $this->weight = $weight;
    }

    function __get($name)
    {
        return $this->{$name} ?? null;
    }
}
