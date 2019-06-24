<?php

namespace IndeedHat\TokenSearch\Test\Indexer;

use IndeedHat\TokenSearch\Indexer\AbstractIndexer;
use IndeedHat\TokenSearch\Indexer\RowIndexer;
use PHPUnit\Framework\TestCase;

class RowIndexerTest extends TestCase
{
    /**
     * @var RowIndexer
     */
    public static $rowIndexer;

    /**
     * @beforeClass
     */
    public static function init(): void
    {
        self::$rowIndexer = new RowIndexer([
            "field 1" => "boring none repeated text",
            "field 2" => "also boring but repeated text, but still boring"
        ]);

        self::$rowIndexer->index();
    }

    /**
     * @test
     */
    public function extendsAbstractIndexer(): void
    {
        $this->assertInstanceOf(AbstractIndexer::class, self::$rowIndexer);
    }

    /**
     * @test
     */
    public function validateField1(): void
    {
        $this->assertEquals("field 1", self::$rowIndexer->fields["field 1"]->id);
        $this->assertEquals([
            "boring" => 1,
            "none" => 1,
            "repeated" => 1,
            "text" => 1
        ], self::$rowIndexer->fields["field 1"]->words);
    }

    /**
     * @test
     */
    public function validateField2(): void
    {
        $this->assertEquals("field 2", self::$rowIndexer->fields["field 2"]->id);
        $this->assertEquals([
            "also" => 1,
            "boring" => 2,
            "but" => 2,
            "repeated" => 1,
            "text," => 1,
            "still" => 1
        ], self::$rowIndexer->fields["field 2"]->words);
    }

    /**
     * @test
     */
    public function validateRowWords(): void
    {
        $this->assertEquals([
            "boring" => 3,
            "none" => 1,
            "repeated" => 2,
            "text" => 1,
            "also" => 1,
            "but" => 2,
            "text," => 1,
            "still" => 1
        ], self::$rowIndexer->words);
    }
}
