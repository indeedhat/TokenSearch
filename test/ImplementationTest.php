<?php

namespace IndeedHat\TokenSearch\Test;

use IndeedHat\TokenSearch\Database\MySQLDiscoveryAdapter;
use IndeedHat\TokenSearch\Database\MySQLStorageAdapter;
use IndeedHat\TokenSearch\Indexer\RowIndexer;
use IndeedHat\TokenSearch\Sorter\DocIdSorter;
use IndeedHat\TokenSearch\TokenSearch;
use IndeedHat\TokenSearch\Tokenizer\WhiteSpaceTokenizer;
use PHPUnit\Framework\TestCase;

class ImplementationTest extends TestCase
{
    /**
     * @var TokenSearch $tksearch
     */
    public static $tksearch;

    public static $key = "tst";

    /**
     * @beforeClass
     */
    public static function init(): void
    {
        $tk = new TokenSearch("tst");

        $tk->withDiscoveryAdapter(
            new MySQLDiscoveryAdapter(
                "localhost",
                "test",
                "root",
                ""
            )
        );

        $tk->withStorageAdapter(
            new MySQLStorageAdapter(
                "localhost",
                "test",
                "root",
                ""
            )
        );

        $tk->withSorter(new DocIdSorter());
        $tk->withTokenizer(new WhiteSpaceTokenizer());

        if ($tk->indexExists()) {
            $tk->removeIndex();
        } 

        self::$tksearch = $tk;
    }

    /**
     * @test
     */
    public function indexCheck(): void
    {
        $this->assertTrue(
            self::$tksearch->createIndex("SELECT * FROM customer_data", "id")
        );
    }

    /**
     * @test
     */
    public function searchCheck(): void
    {
        $this->expectNotToPerformAssertions();
        var_dump(self::$tksearch->search("jane"));
    }
}
