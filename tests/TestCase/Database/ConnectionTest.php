<?php declare(strict_types=1);

namespace Lightning\Test\Database;

use PDO;
use Exception;
use PDOException;
use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;
use Lightning\Database\Statement;
use function Lightning\Dotenv\env;
use Lightning\Database\Connection;
use Lightning\Database\PdoFactory;
use Lightning\Database\PdoFactoryInterface;
use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\TagsFixture;
use Lightning\QueryBuilder\QueryBuilder;
use Lightning\TestSuite\LoggerTestTrait;
use Lightning\Test\Fixture\ArticlesFixture;


class NoExceptionConfigPdoFactory implements PdoFactoryInterface
{
    public function create(): PDO
    {
        return new PDO(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));
    }

}

final class ConnectionTest extends TestCase
{
    use LoggerTestTrait;

    private PdoFactory $pdoFactory;
    private PDO $pdo;

    public function setUp(): void
    {
        $this->pdoFactory = new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),true);
        $this->pdo = $this->pdoFactory->create();

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([
            ArticlesFixture::class,
            TagsFixture::class
        ]);

        $this->setLogger($this->createLogger());
    }

    private function createConnection(bool $useExceptions = true): Connection
    {

        $connection = new Connection($useExceptions ? $this->pdoFactory : new NoExceptionConfigPdoFactory());
        $connection->setLogger($this->getLogger());
        $connection->connect();


        return $connection;
    }

    public function testGetPDO(): void
    {
        $connection = new Connection($this->pdoFactory);
        $this->assertNull($connection->getPdo());
        $connection->connect();
        $this->assertInstanceOf(PDO::class,$connection->getPdo());
    }


    public function testIsConnected(): void 
    {
        $connection = new Connection($this->pdoFactory);
        $this->assertFalse($connection->isConnected());

        
        $connection->connect();
        $this->assertTrue($connection->isConnected());

        $connection->disconnect();
        $this->assertFalse($connection->isConnected());
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
        $connection = $this->createConnection(true);
        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->inTransaction());
        $this->assertFalse($connection->beginTransaction());

        $this->assertLogHasMessage('BEGIN', LogLevel::DEBUG);
        $this->assertLogMessagesCount(1);

        $connection->rollback();
    }

    public function testCommitTransactionTest(): void
    {
        $connection = $this->createConnection(true);

        $this->assertFalse($connection->commit());
        $this->assertFalse($connection->inTransaction());

        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->commit());
        $this->assertFalse($connection->inTransaction());

        $this->assertLogHasMessage('COMMIT', LogLevel::DEBUG);
        $this->assertLogMessagesCount(2);

        $connection->rollback();
    }

    public function testRollbackTransactionTest(): void
    {
        $connection = $this->createConnection(true);
        $this->assertFalse($connection->rollback());

        $connection->beginTransaction();

        $this->assertTrue($connection->rollback());

        $this->assertLogHasMessage('ROLLBACK', LogLevel::DEBUG);
        $this->assertLogMessagesCount(2);
    }

    public function testExecute(): void
    {
        $connection = $this->createConnection(true);

        $statement = $connection->execute('SELECT * FROM articles');
        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertEquals('SELECT * FROM articles', $statement->getQueryString());

        $this->assertLogHasMessage('SELECT * FROM articles', LogLevel::DEBUG);
        $this->assertLogMessagesCount(1);
    }

    public function testExecuteWithParams(): void
    {
        $connection = $this->createConnection(true);

        $statement = $connection->execute('SELECT * FROM articles WHERE id = ?', [1000]);
        $this->assertEquals('SELECT * FROM articles WHERE id = ?', $statement->getQueryString());
        $this->assertCount(1, $statement->fetchAll());

        $this->assertLogHasMessage('SELECT * FROM articles WHERE id = 1000', LogLevel::DEBUG);
        $this->assertLogMessagesCount(1);
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
        $connection = $this->createConnection(true);

        $result = $connection->execute('SELECT * FROM articles');

        $this->assertLogHasMessage('SELECT * FROM articles', LogLevel::DEBUG);
        $this->assertLogMessagesCount(1);
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
