<?php

namespace IndeedHat\TokenSearch\Test;

use IndeedHat\TokenSearch\Database\MySQLStorageDriver;
use IndeedHat\TokenSearch\Indexer\RowIndexer;
use PHPUnit\Framework\TestCase;

class ImplementationTest extends TestCase
{
    /**
     * @var MySQLStorageDriver $driver
     */
    public static $driver;
    public static $key = "tst";

    /**
     * @beforeClass
     */
    public static function init(): void
    {
        self::$driver = new MySQLStorageDriver("localhost", "test", "root", "");
        
        if (!self::$driver->schemaExists(self::$key)) {
            print "creating schema";
            self::$driver->createSchema(self::$key);
        }
    }

    public function exampleData(): array
    {
        return [
            [
                1,
                [
                    "field.1" => "field 1 text",
                    "field.2" => "field 2 text"
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider exampleData
     */
    public function doTheThing(int $id, array $fields): void
    {
        $this->expectNotToPerformAssertions();
        $indexer = new RowIndexer($fields, $id);
        $indexer->index();
        self::$driver->insertRow(self::$key, $indexer);
    }
}
