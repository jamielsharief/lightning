<?php declare(strict_types=1);

namespace Lightning\Test\Fixture\SqlDialect;

use PHPUnit\Framework\TestCase;
use Lightning\Fixture\SqlDialect\SqliteDialect;

final class SqliteDialectTest extends TestCase
{
    public function testEnableForeignKeyConstraints(): void
    {
        $this->assertEquals(
            ['PRAGMA foreign_keys = ON'],
            (new SqliteDialect())->enableForeignKeyConstraints()
        );
    }

    public function testDisableForeignKeyConstraints(): void
    {
        $this->assertEquals(
            ['PRAGMA foreign_keys = OFF'],
            (new SqliteDialect())->disableForeignKeyConstraints()
        );
    }

    public function testTruncate(): void
    {
        $this->assertEquals(
            [
                'DELETE FROM "articles"',
                'DELETE FROM sqlite_sequence WHERE name = "articles"'
            ],
            (new SqliteDialect())->truncate('articles')
        );
    }

    public function testQuoteIdentifier(): void
    {
        $this->assertEquals(
            '"articles"',
            (new SqliteDialect())->quoteIdentifier('articles')
        );
    }

    public function testResetAutoincrement(): void
    {
        $this->assertEquals(
            ['SQLITE_SEQUENCE SET SEQ = 1000 WHERE NAME = "articles"'],
            (new SqliteDialect())->resetAutoIncrement('articles', 1000)
        );
    }
}
