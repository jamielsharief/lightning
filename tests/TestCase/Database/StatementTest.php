<?php declare(strict_types=1);

namespace Lightning\Test\Database;

use PDO;
use PHPUnit\Framework\TestCase;

use Lightning\Database\Statement;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\TagsFixture;
use Lightning\Test\Fixture\ArticlesFixture;

final class StatementTest extends TestCase
{
    private PDO $pdo;

    public function setUp(): void
    {
        $pdoFactory = new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),true);
        $this->pdo = $pdoFactory->create();

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([
            ArticlesFixture::class,
            TagsFixture::class
        ]);
    }

    public function testExecute(): void
    {
        $statement = $this->pdo->query('SELECT * FROM articles');
        $statement = new Statement($statement);

        $this->assertTrue($statement->execute());
    }

    public function testGetQueryString(): void
    {
        $statement = $this->pdo->query('SELECT * FROM articles');
        $statement = new Statement($statement);

        $this->assertEquals(
            'SELECT * FROM articles',
            $statement->getQueryString()
        );
    }

    public function testStringable(): void
    {
        $statement = $this->pdo->query('SELECT * FROM articles');
        $statement = new Statement($statement);

        $this->assertEquals(
            'SELECT * FROM articles',
            (string) $statement
        );
    }

    public function testRowCount(): void
    {
        $statement = $this->pdo->prepare('DELETE FROM articles');
        $statement = new Statement($statement);

        $this->assertEquals(0, $statement->rowCount());

        $statement->execute();

        $this->assertEquals(3, $statement->rowCount());
    }

    public function testCountable(): void
    {
        $statement = $this->pdo->prepare('DELETE FROM articles');
        $statement = new Statement($statement);

        $this->assertCount(0, $statement);

        $statement->execute();

        $this->assertCount(3, $statement);
    }

    public function testColumnCount(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles');
        $statement = new Statement($statement);

        $this->assertEquals(0, $statement->columnCount());

        $statement->execute();

        $this->assertEquals(6, $statement->columnCount());
    }

    public function testCloseCursor(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles');
        $statement = new Statement($statement);

        $this->assertTrue($statement->closeCursor());

        $statement->execute();

        $this->assertTrue($statement->closeCursor()); // Always true
    }

    public function testFetch(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles LIMIT 2');
        $statement = new Statement($statement);

        $this->assertFalse($statement->fetch());

        $statement->execute();

        $result = $statement->fetch();
        $this->assertTrue(isset($result['id'])); // Check its using the default fetch type
    }

    public function testFetchAssociative(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles LIMIT 2');
        $statement = new Statement($statement);

        $this->assertFalse($statement->fetchAssociative());

        $statement->execute();

        $result = $statement->fetchAssociative();
        $this->assertTrue(isset($result['id'])); // Check its using the default fetch type
    }

    public function testFetchNumeric(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles LIMIT 2');
        $statement = new Statement($statement);

        $this->assertFalse($statement->fetchNumeric());

        $statement->execute();

        $result = $statement->fetchNumeric();
        $this->assertEquals('Article #1', $result[1]);
    }

    public function testFetchObject(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles LIMIT 2');
        $statement = new Statement($statement);

        $this->assertFalse($statement->fetchObject());

        $statement->execute();

        $result = $statement->fetchObject();

        $this->assertTrue(is_object($result));
        $this->assertTrue(isset($result->id)); // Check its using the default fetch type
    }

    public function testFetchAll(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles LIMIT 2');
        $statement = new Statement($statement);

        $this->assertCount(0, $statement->fetchAll());

        $statement->execute();

        $result = $statement->fetchAll();
        $this->assertCount(2, $result);
        $this->assertTrue(isset($result[0]['id'])); // Check its using the default fetch type
    }

    public function testFetchAllAssociative(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles LIMIT 2');
        $statement = new Statement($statement);

        $this->assertCount(0, $statement->fetchAllAssociative());

        $statement->execute();

        $result = $statement->fetchAllAssociative();
        $this->assertCount(2, $result);
        $this->assertTrue(isset($result[0]['id'])); // Check its using the default fetch type
    }

    public function testFetchAllObject(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles LIMIT 2');
        $statement = new Statement($statement);

        $this->assertCount(0, $statement->fetchAllObject());

        $statement->execute();

        $result = $statement->fetchAllObject();
        $this->assertCount(2, $result);
        $this->assertEquals(1000, $result[0]->id);
    }

    public function testFetchAllNumeric(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles LIMIT 2');
        $statement = new Statement($statement);

        $this->assertCount(0, $statement->fetchAllNumeric());

        $statement->execute();

        $result = $statement->fetchAllNumeric();
        $this->assertCount(2, $result);
        $this->assertEquals('Article #1', $result[0][1]);
    }

    public function testIteratorAggregate(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles');
        $statement = new Statement($statement);

        $data = iterator_to_array($statement->getIterator());
        $this->assertTrue(isset($data[0]['id'])); // Check its using the default fetch type

        $statement->execute();
        $this->assertEquals(3, iterator_count($statement->getIterator())); // test call again
    }

    public function testFetchColumn(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles LIMIT 2');
        $statement = new Statement($statement);

        $this->assertFalse($statement->fetchColumn(1));

        $statement->execute();

        $this->assertEquals('Article #1', $statement->fetchColumn(1));
    }

    public function testSetFetchMode(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles LIMIT 2');
        $statement = new Statement($statement);
        $statement->setFetchMode(PDO::FETCH_OBJ);

        $statement->execute();

        $result = $statement->fetch();

        $this->assertTrue(is_object($result));
        $this->assertTrue(isset($result->id)); // Check its using the default fetch type
    }

    public function testBind(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles WHERE id != ? AND title <> ?');
        $statement = new Statement($statement);

        $statement->bind([
            1000,
            'A description for article #3'
        ]);

        $statement->execute();
        $this->assertCount(2, $statement->fetchAll());
    }

    public function testBindWithTypes(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles WHERE id != ? AND title <> ?');
        $statement = new Statement($statement);

        $statement->bind([
            1000,
            'A description for article #3'
        ], [PDO::PARAM_INT,PDO::PARAM_STR]);

        $statement->execute();
        $this->assertCount(2, $statement->fetchAll());
    }

    public function testBindNamedWithTypes(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles WHERE id != :id AND title <> :title');
        $statement = new Statement($statement);

        $statement->bind([
            'id' => 1000,
            'title' => 'A description for article #3'
        ], [PDO::PARAM_INT,PDO::PARAM_STR]);

        $statement->execute();
        $this->assertCount(2, $statement->fetchAll());
    }

    public function testBindValue(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles WHERE id != ? AND title <> ?');
        $statement = new Statement($statement);

        $statement->bindValue(1, 1000);
        $statement->bindValue(2, 'A description for article #3');

        $statement->execute();
        $this->assertCount(2, $statement->fetchAll());
    }

    public function testBindValueNamed(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles WHERE id != :id AND title <> :title');
        $statement = new Statement($statement);

        $statement->bindValue('id', 1000);
        $statement->bindValue('title', 'A description for article #3');

        $statement->execute();
        $this->assertCount(2, $statement->fetchAll());
    }

    public function testBindValueNamedWithTypes(): void
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles WHERE id != :id AND title <> :title');
        $statement = new Statement($statement);

        $statement->bindValue('id', 1000, PDO::PARAM_INT);
        $statement->bindValue('title', 'A description for article #3', PDO::PARAM_STR);

        $statement->execute();
        $this->assertCount(2, $statement->fetchAll());
    }
}
