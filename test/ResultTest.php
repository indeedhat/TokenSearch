<?php

namespace IndeedHat\TokenSearch\Test;

use IndeedHat\TokenSearch\Result;
use Exception;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    /**
     * @test
     */
    public function canReturnCorrectProps(): void
    {
        $result = new Result(1, 3);

        $this->assertEquals(1, $result->docId);
        $this->assertEquals(3, $result->weight);
    }

    /**
     * @test
     */
    public function thowsOnSettingValues(): void
    {
        $result = new Result(2, 4);

        $this->expectException(Exception::class);
        $result->docId = 4;
    }
}
