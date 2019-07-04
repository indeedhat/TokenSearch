<?php

namespace IndeedHat\TokenSearch;

use Exception;

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

    /**
     * @param
     */
    public function __construct(int $docId, float $weight)
    {
        $this->docId  = $docId;
        $this->weight = $weight;
    }

    public function __get($name)
    {
        return $this->{$name} ?? null;
    }

    public function __set($name, $val)
    {
        throw new Exception("results are immutable");
    }
}
