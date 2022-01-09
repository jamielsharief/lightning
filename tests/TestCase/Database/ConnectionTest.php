<?php declare(strict_types=1);

namespace Lightning\Test\Database;

use PDO;
use Exception;
use PDOException;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Lightning\Cache\MemoryCache;
use Lightning\Database\Statement;
use function Lightning\Dotenv\env;
use Lightning\Database\Connection;
use Lightning\Database\PdoFactory;
use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\TagsFixture;
use Lightning\QueryBuilder\QueryBuilder;
use Lightning\TestSuite\Stubs\LoggerStub;
use Lightning\Test\Fixture\ArticlesFixture;

final class ConnectionTest extends TestCase
{
    private PDO $pdo;
    public function setUp(): void
    {
        $pdoFactory = new PdoFactory();
        $this->pdo = $pdoFactory->create(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([
            ArticlesFixture::class,
            TagsFixture::class
        ]);
    }

    private function createConnection(bool $useExceptions = true, ?LoggerInterface $logger = null): Connection
    {
        $pdo = $useExceptions ? $this->pdo : new PDO(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));

        return new Connection($pdo, $logger);
    }

    public function testGetPDO(): void
    {
        $this->assertInstanceOf(PDO::class, $this->createConnection()->getPdo());
    }

    public function testGetDriver(): void
    {
        $this->assertContains($this->createConnection()->getDriver(), ['mysql','sqlite','pgsql']);
    }

    public function testGetLastInsertId(): void
    {
        $connection = $this->createConnection();

        switch ($connection->getDriver()) {
            case 'mysql':
                $this->assertNull($connection->getLastInsertId());

            break;
            case 'sqlite':
                $this->assertEquals(2002, $connection->getLastInsertId());

            break;
        }
    }

    public function testPrepare(): void
    {
        $connection = $this->createConnection();
        $this->assertInstanceOf(Statement::class, $connection->execute('SELECT * FROM articles'));
    }

    public function testPrepareQuery(): void
    {
        $connection = $this->createConnection();
        $query = (new QueryBuilder())->select(['*'])->from('articles');
        $this->assertInstanceOf(Statement::class, $connection->execute($query));
    }

    public function testInTransaction(): void
    {
        $connection = $this->createConnection();
        $this->assertFalse($connection->inTransaction());
    }

    public function testBeginTransactionTest(): void
    {
        $logger = new LoggerStub();
        $connection = $this->createConnection(true, $logger);
        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->inTransaction());
        $this->assertFalse($connection->beginTransaction());

        $this->assertCount(1, $logger->getLogged());
        $this->assertEquals('debug', $logger->getLogged()[0][0]);
        $this->assertEquals('BEGIN', $logger->getLogged()[0][1]);

        $connection->rollback();
    }

    public function testCommitTransactionTest(): void
    {
        $logger = new LoggerStub();
        $connection = $this->createConnection(true, $logger);

        $this->assertFalse($connection->commit());
        $this->assertFalse($connection->inTransaction());

        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->commit());
        $this->assertFalse($connection->inTransaction());

        $this->assertCount(2, $logger->getLogged());
        $this->assertEquals('debug', $logger->getLogged()[1][0]);
        $this->assertEquals('COMMIT', $logger->getLogged()[1][1]);

        $connection->rollback();
    }

    public function testRollbackTransactionTest(): void
    {
        $logger = new LoggerStub();
        $connection = $this->createConnection(true, $logger);
        $this->assertFalse($connection->rollback());

        $connection->beginTransaction();

        $this->assertTrue($connection->rollback());

        $this->assertCount(2, $logger->getLogged());
        $this->assertEquals('debug', $logger->getLogged()[1][0]);
        $this->assertEquals('ROLLBACK', $logger->getLogged()[1][1]);
    }

    public function testExecute(): void
    {
        $logger = new LoggerStub();
        $connection = $this->createConnection(true, $logger);

        $statement = $connection->execute('SELECT * FROM articles');
        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertEquals('SELECT * FROM articles', $statement->getQueryString());

        $this->assertCount(1, $logger->getLogged());
        $this->assertEquals('debug', $logger->getLogged()[0][0]);
        $this->assertEquals('SELECT * FROM articles', $logger->getLogged()[0][1]);
    }

    public function testExecuteWithParams(): void
    {
        $logger = new LoggerStub();
        $connection = $this->createConnection(true, $logger);

        $statement = $connection->execute('SELECT * FROM articles WHERE id = ?', [1000]);
        $this->assertEquals('SELECT * FROM articles WHERE id = ?', $statement->getQueryString());
        $this->assertCount(1, $statement->fetchAll());

        $this->assertCount(1, $logger->getLogged());
        $this->assertEquals('debug', $logger->getLogged()[0][0]);
        $this->assertEquals('SELECT * FROM articles WHERE id = 1000', $logger->getLogged()[0][1]);
    }

    public function testTransaction(): void
    {
        $connection = $this->createConnection();

        $connection->transaction(function (Connection $connection) {
            $connection->execute('UPDATE articles SET title = ? WHERE id = 1000', ['foo']);
        });

        $this->assertEquals(
            'foo',
            $connection->execute('SELECT title FROM articles WHERE id = ?', [1000])->fetchColumn(0)
        );
    }

    /**
     * @depends testTransaction
     */
    public function testTransactionRollback(): void
    {
        $connection = $this->createConnection();

        try {
            $connection->transaction(function (Connection $connection) {
                $connection->execute('UPDATE articles SET title = ? WHERE id = 1000', ['foo']);

                throw new Exception('Undo');
            });
        } catch (Exception $exception) {
        }

        $this->assertEquals(
            'Article #1',
            $connection->execute('SELECT title FROM articles WHERE id = ?', [1000])->fetchColumn(0)
        );
    }

    /**
    * @depends testTransaction
    */
    public function testTransactionRollbackCancel(): void
    {
        $connection = $this->createConnection();

        $connection->transaction(function (Connection $connection) {
            $connection->execute('UPDATE articles SET title = ? WHERE id = 1000', ['foo']);

            return false;
        });

        $this->assertEquals(
            'Article #1',
            $connection->execute('SELECT title FROM articles WHERE id = ?', [1000])->fetchColumn(0)
        );
    }

    public function testExecuteWasLogged(): void
    {
        $logger = new LoggerStub();
        $connection = $this->createConnection(true, $logger);

        $result = $connection->execute('SELECT * FROM articles');

        $this->assertCount(1, $logger->getLogged());
        $this->assertEquals('debug', $logger->getLogged()[0][0]);
    }

    // public function testExecuteCache()
    // {
    //     $cache = new MemoryCache();
    //     $connection = $this->createConnection(true, $cache);

    //     $result = $connection->execute('SELECT * FROM articles WHERE id = ?', [1000], ['cache' => true]);
    //     $this->assertTrue($cache->has('623830ea9e449f9a5c3aa470ed9b7701312fe8e1'));

    //     // Modifiy the cached version so we know thats its the same one
    //     $cache->get('623830ea9e449f9a5c3aa470ed9b7701312fe8e1')->foo = 'bar';

    //     $result = $connection->execute('SELECT * FROM articles WHERE id = ?', [1000], ['cache' => true]);
    //     $this->assertEquals('bar', $result->foo);
    // }

    public function testExecuteError(): void
    {
        $this->expectException(PDOException::class);
        $this->createConnection()->execute('SELECT * FROM foo');
    }

    public function testInsertWithPlaceHolders(): void
    {
        $connection = $this->createConnection();

        $statement = $connection->execute('INSERT INTO tags (name,created_at,updated_at) VALUES ( ? , ? , ?)', [
            'test',
            '2021-10-31 14:30:00',
            '2021-10-31 14:30:00'
        ]);

        $this->assertEquals(1, $statement->rowCount());
        $expected = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql' ? 1 : 2003;
        $this->assertEquals((string) $expected, $connection->getLastInsertId());
    }

    public function testInsertWithNamedPlaceHolders(): void
    {
        $connection = $this->createConnection();

        $statement = $connection->execute('INSERT INTO tags (name,created_at,updated_at) VALUES ( :name,:created_at, :updated_at)', [
            'name' => 'test',
            'created_at' => '2021-10-31 14:30:00',
            'updated_at' => '2021-10-31 14:30:00'
        ]);

        $this->assertEquals(1, $statement->rowCount());
        $expected = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql' ? 1 : 2003;
        $this->assertEquals((string) $expected, $connection->getLastInsertId());
    }

    public function testInsert(): void
    {
        $connection = $this->createConnection();

        $this->assertTrue(
            $connection->insert('tags', [
                'name' => 'new',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            );
    }

    public function testUpdate(): void
    {
        $connection = $this->createConnection();

        $this->assertEquals(3,
            $connection->update('articles', [
                'title' => 'all names',
            ])
        );

        $this->assertEquals(1,
        $connection->update('articles', [
            'title' => 'just this one',
        ], ['id' => 1000])
    );
    }

    public function testDelete(): void
    {
        $connection = $this->createConnection();

        $this->assertEquals(3,
            $connection->delete('tags')
        );

        $this->assertEquals(1,
            $connection->delete('articles', ['id' => 1000])
        );
    }
}
