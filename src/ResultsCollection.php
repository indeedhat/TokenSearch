<?php

namespace IndeedHat\TokenSearch;

use Exception;
use Countable;

class ResultsCollection implements Countable
{
    const ORDER_ASC = 1;
    const ORDER_DESC = -1;

    const ORDER_BY_WEIGHT = "weight";
    const ORDER_BY_ID = "docId";

    /**
     * @var array
     */
    private $results;

    /**
     * @var int
     */
    private $totalDocs;

    /**
     * @throws Exception
     */
    function __construct(array $results, int $totalDocs)
    {
        foreach ($results as $result) {
            if (!$result instanceof Result) {
                throw new Exception("invalid results array");
            }
        }

        $this->results = $results;
        $this->totalDocs = $totalDocs;
    }

    public function ids(): array
    {
        return array_map(function(Result $result) {
            return $result->docId;
        }, $this->results);
    }

    public function results(): array
    {
        return $this->results;
    }

    public function take(int $count): ResultsCollection
    {
        return new ResultsCollection(array_slice($this->results, 0, $count), $this->totalDocs);
    }

    public function reorder(string $order = self::ORDER_DESC, $orderBy = self::ORDER_BY_WEIGHT): void
    {
        usort($this->results, function (Result $a, Result $b) use ($order, $orderBy) {
            if ($a->{$orderBy} == $b->{$orderBy}) {
                return 0;
            }

            return $a->{$orderBy} > $b->{$orderBy} ? $order : -$order;
        });
    }
    
    public function totalDocs(): int
    {
        return $this->totalDocs;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->results);
    }
}
