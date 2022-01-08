<?php declare(strict_types=1);

namespace Lightning\Test\Fixture\SqlDialect;

use PHPUnit\Framework\TestCase;
use Lightning\Fixture\SqlDialect\MysqlDialect;

final class MySqlDialectTest extends TestCase
{
    public function testEnableForeignKeyConstraints(): void
    {
        $this->assertEquals(
            ['SET FOREIGN_KEY_CHECKS = 1'],
            (new MysqlDialect())->enableForeignKeyConstraints()
        );
    }

    public function testDisableForeignKeyConstraints(): void
    {
        $this->assertEquals(
            ['SET FOREIGN_KEY_CHECKS = 0'],
            (new MysqlDialect())->disableForeignKeyConstraints()
        );
    }

    public function testTruncate(): void
    {
        $this->assertEquals(
            ['TRUNCATE TABLE `articles`'],
            (new MysqlDialect())->truncate('articles')
        );
    }

    public function testQuoteIdentifier(): void
    {
        $this->assertEquals(
            '`articles`',
            (new MysqlDialect())->quoteIdentifier('articles')
        );
    }

    public function testResetAutoincrement(): void
    {
        $this->assertEquals(
            ['ALTER TABLE `articles` AUTO_INCREMENT = 1000'],
            (new MysqlDialect())->resetAutoIncrement('articles', 1000)
        );
    }
}
