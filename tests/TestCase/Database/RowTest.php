<?php declare(strict_types=1);

namespace Lightning\Test\Database;

use PDO;
use Lightning\Database\Row;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\Connection;

use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\ArticlesFixture;

final class RowTest extends TestCase
{
    private PDO $pdo;

    public function setUp(): void
    {
        $this->pdo = new PDO(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));
        // $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, [PDO::FETCH_CLASS, Row::class]);

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([
            ArticlesFixture::class
        ]);
    }

    public function testIsset()
    {
        $row = new Row();
        $row->title = 'Article';

        $this->assertTrue($row->has('title'));
        $this->assertTrue(isset($row->title));
        $this->assertTrue(isset($row['title']));

        $this->assertFalse($row->has('name'));
        $this->assertFalse(isset($row->name));
        $this->assertFalse(isset($row['name']));
    }

    public function testUnset()
    {
        $row = new Row();
        $row->title = 'Article';

        $row->unset('title');
        $this->assertFalse($row->has('title'));

        $row = new Row(['title' => 'Article']);
        unset($row->title);
        $this->assertFalse(isset($row->title));

        $row = new Row(['title' => 'Article']);
        unset($row['title']);
        $this->assertFalse(isset($row['title']));

        unset($row->foo); // Ensure no errors
    }

    public function testGet(): void
    {
        $row = new Row();
        $row->title = 'Article';
        $this->assertEquals('Article', $row->get('title'));

        $this->assertEquals('Article', $row->title);

        $this->assertEquals('Article', $row['title']);

        $this->assertNull($row->foo);
    }

    public function testSet(): void
    {
        $row = new Row();
        $row->title = 'Foo';

        $this->assertEquals('Foo', $row->get('title'));

        $row = new Row();
        $row->id = 1234;
        $this->assertEquals(1234, $row->id);

        $row = new Row();
        $row['foo'] = 'bar';
        $this->assertEquals('bar', $row['foo']);
    }

    public function testWorksWithPDO(): void
    {
        $connection = new Connection($this->pdo);

        $result = $connection->execute('SELECT * FROM articles')->fetchObject(Row::class);

        $this->assertInstanceOf(Row::class, $result);
        $this->assertEquals('Article #1', $result->title);
    }

    public function testToString()
    {
        $row = new Row();
        $row->title = 'Article';

        $author = new Row();
        $author->name = 'Jon';
        $row->author = $author;

        $tag = new Row();
        $tag->title = 'new';
        $row->tags = [
            $tag
        ];

        $this->assertEquals(
            '{"title":"Article","author":{"name":"Jon"},"tags":[{"title":"new"}]}',
            $row->toString()
        );

        $this->assertEquals(
            '{"title":"Article","author":{"name":"Jon"},"tags":[{"title":"new"}]}',
            (string) $row
        );
    }

    public function testFromState(): void
    {
        $row = Row::fromState(['title' => 'Article','id' => 1234]);
        $this->assertEquals('Article', $row->title);
        $this->assertEquals(1234, $row->id);
    }

    public function testToArray()
    {
        $row = new Row();
        $row->title = 'Article';

        $author = new Row();
        $author->name = 'Jon';
        $row->author = $author;

        $tag = new Row();
        $tag->title = 'new';
        $row->tags = [
            $tag
        ];
        $this->assertEquals(
           json_decode('{"title":"Article","author":{"name":"Jon"},"tags":[{"title":"new"}]}', true),
            $row->toArray()
        );
    }
}
