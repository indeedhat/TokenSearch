<?php

namespace IndeedHat\TokenSearch\Test\Indexer;

use IndeedHat\TokenSearch\Indexer\AbstractIndexer;
use IndeedHat\TokenSearch\Indexer\FieldIndexer;
use PHPUnit\Framework\TestCase;

class FieldIndexerTest extends TestCase
{
    /**
     * @var FieldIndexer
     */
    public static $fieldIndexer;

    /**
     * @beforeClass
     */
    public static function init()
    {
        self::$fieldIndexer = new FieldIndexer(
            ["a small bit of text with a small amount of duplicates"],
            "fieldID"
        );
    }

    /**
     * @test
     */
    public function extendsAbstractIndexer(): void
    {
        $this->assertInstanceOf(AbstractIndexer::class, self::$fieldIndexer);
    }

    /**
     * @test
     */
    public function checkIndexerID()
    {
        $this->assertEquals("fieldID", self::$fieldIndexer->id);
    }

    /**
     * @test
     */
    public function generatesCorrectWordCounts(): void
    {
        self::$fieldIndexer->index();

        $this->assertEquals(
            [
                "a" => 2,
                "small" => 2,
                "bit" => 1,
                "of" => 2,
                "text" => 1,
                "with" => 1,
                "amount" => 1,
                "duplicates" => 1
            ],
            self::$fieldIndexer->words
        );
    }
}
