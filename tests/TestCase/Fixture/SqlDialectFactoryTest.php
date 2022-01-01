<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\Fixture\SqlDialectFactory;
use Lightning\Fixture\SqlDialect\MysqlDialect;
use Lightning\Fixture\SqlDialect\SqliteDialect;
use Lightning\Fixture\SqlDialect\PostgresDialect;

final class SqlDialectFactoryTest extends TestCase
{
    /*
    DB_URL=mysql:host=mysql;port=3306;dbname=lightning
#DB_URL=pgsql:host=pgsql;port=5432;dbname=lightning;schema=public
#DB_URL=sqlite:dbname=database/db.sqlite
*/
    public function testCreateMySql()
    {
        $this->assertInstanceOf(MysqlDialect::class, (new SqlDialectFactory())->create('mysql'));
    }

    public function testCreatePostgres()
    {
        $this->assertInstanceOf(PostgresDialect::class, (new SqlDialectFactory())->create('pgsql'));
    }

    public function testCreateSqlite()
    {
        $this->assertInstanceOf(SqliteDialect::class, (new SqlDialectFactory())->create('sqlite'));
    }

    public function testUnkownDriver()
    {
        $this->expectException(InvalidArgumentException::class);
        (new SqlDialectFactory())->create('mango');
    }
}
