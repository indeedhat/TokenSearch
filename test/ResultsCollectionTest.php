<?php

namespace IndeedHat\TokenSearch\Test;

use IndeedHat\TokenSearch\Result;
use IndeedHat\TokenSearch\ResultsCollection;
use PHPUnit\Framework\TestCase;

class ResultsCollectionTest extends TestCase
{
    /**
     * @var test
     */
    public function throwsOnNoneResultEntries(): void
    {
        $this->expectException(Exception::class);

        $rows = [
            ["some"],
            ["data"]
        ];

        $collection = new ResultsCollection($rows, count($rows));
    }

    /**
     * @test
     */
    public function canCountResults(): void
    {
        $collection = $this->setupCollection();

        $this->assertEquals(10, $collection->count());
    }

    /**
     * @test
     */
    public function canReturnResults(): void
    {
        $collection = $this->setupCollection();

        $this->assertEquals($this->buildResults()[0], $collection->results());
    }

    /**
     * @test
     */
    public function canReturnDocIds(): void
    {
        $expectedIds = range(1, 10);
        $collection = $this->setupCollection();

        $this->assertEquals($expectedIds, $collection->ids());
    }

    /**
     * @test
     */
    public function canTakenNResults(): void
    {
        $take = 5;
        $collection = $this->setupCollection();

        $this->assertEquals($take, $collection->take($take)->count());
    }

    /**
     * @test
     */
    public function canReorderResultsByWeight(): void
    {
        $collection = $this->setupCollection();

        $orderedDesc = $collection->results();
        usort($orderedDesc, function(Result $a, Result $b) {
            if ($a->weight == $b->weight) {
                return 0;
            }
            return $a->weight > $b->weight ? -1 : 1;
        });

        $collection->reorder(ResultsCollection::ORDER_DESC, ResultsCollection::ORDER_BY_WEIGHT);
        $this->assertEquals($orderedDesc, $collection->results());

        $orderAsc = $collection->results();
        usort($orderAsc, function(Result $a, Result $b) {
            if ($a->weight == $b->weight) {
                return 0;
            }
            return $a->weight > $b->weight ? 1 : -1;
        });

        $collection->reorder(ResultsCollection::ORDER_ASC, ResultsCollection::ORDER_BY_WEIGHT);
        $this->assertEquals($orderAsc, $collection->results());
    }

    /**
     * @test
     */
    public function canReorderResultsByDocId(): void
    {
        $collection = $this->setupCollection();

        $orderedDesc = $collection->results();
        usort($orderedDesc, function(Result $a, Result $b) {
            if ($a->docId == $b->docId) {
                return 0;
            }
            return $a->docId > $b->docId ? -1 : 1;
        });

        $collection->reorder(ResultsCollection::ORDER_DESC, ResultsCollection::ORDER_BY_ID);
        $this->assertEquals($orderedDesc, $collection->results());

        $orderAsc = $collection->results();
        usort($orderAsc, function(Result $a, Result $b) {
            if ($a->docId == $b->docId) {
                return 0;
            }
            return $a->docId > $b->docId ? 1 : -1;
        });

        $collection->reorder(ResultsCollection::ORDER_ASC, ResultsCollection::ORDER_BY_ID);
        $this->assertEquals($orderAsc, $collection->results());
    }

    private function buildResults(): array
    {
        $results = [];

        for ($i = 0; $i < 10; $i++) {
            $results[] = new Result($i + 1, 10 - $i);
        }

        return [$results, count($results)];
    }

    private function setupCollection(): ResultsCollection
    {
        return new ResultsCollection(...$this->buildResults());
    }
}
