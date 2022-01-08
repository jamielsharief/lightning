<?php declare(strict_types=1);

namespace Lightning\Test\Fixture\SqlDialect;

use PHPUnit\Framework\TestCase;
use Lightning\Fixture\SqlDialect\PostgresDialect;

final class PostgresDialectTest extends TestCase
{
    public function testEnableForeignKeyConstraints(): void
    {
        $this->assertEquals(
            ['SET CONSTRAINTS ALL DEFERRED'],
            (new PostgresDialect())->enableForeignKeyConstraints()
        );
    }

    public function testDisableForeignKeyConstraints(): void
    {
        $this->assertEquals(
            ['SET CONSTRAINTS ALL IMMEDIATE'],
            (new PostgresDialect())->disableForeignKeyConstraints()
        );
    }

    public function testTruncate(): void
    {
        $this->assertEquals(
            [
                'TRUNCATE TABLE "articles" RESTART IDENTITY CASCADE'
            ],
            (new PostgresDialect())->truncate('articles')
        );
    }

    public function testQuoteIdentifier(): void
    {
        $this->assertEquals(
            '"articles"',
            (new PostgresDialect())->quoteIdentifier('articles')
        );
    }

    public function testResetAutoincrement(): void
    {
        $this->assertEquals(
            ['ALTER SEQUENCE articles_xid_seq RESTART WITH 1000'],
            (new PostgresDialect())->resetAutoIncrement('articles', 1000, 'xid')
        );
    }
}
