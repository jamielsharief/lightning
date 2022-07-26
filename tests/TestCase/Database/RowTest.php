<?php declare(strict_types=1);

namespace Lightning\Test\Database;

use PDO;
use Lightning\Database\Row;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\Connection;
use Lightning\Database\PdoFactory;
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
        $row->set('foo', 'bar');
        $this->assertEquals('bar', $row->get('foo'));

        $row->set(['bar' => 'foo']);
        $this->assertEquals('foo', $row->get('bar'));
    }

    public function testArrayAccess(): void
    {
        $row = new Row([]);

        $row['key'] = 'value';
        $this->assertEquals('value', $row['key']);
        $this->assertTrue(isset($row['key']));
        unset($row['key']);
        $this->assertFalse(isset($row['key']));

        $row = new Row([]);
        $row[] = 'foo';
        $this->assertEquals('foo', $row[0]);
    }

    public function testWorksWithPDO(): void
    {
        $connection = new Connection(new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD')));
        $connection->connect();
        
        $result = $connection->execute('SELECT * FROM articles')->fetchObject(Row::class);

        $this->assertInstanceOf(Row::class, $result);
        $this->assertEquals('Article #1', $result->title);
    }

    public function testToString()
    {
        $this->assertEquals(
            '{"title":"Article","author":{"name":"Jon"},"tags":[{"title":"new"}]}',
            $this->createRowWithAssociatedData()->toString()
        );
    }

    public function testStringable()
    {
        $this->assertEquals(
            '{"title":"Article","author":{"name":"Jon"},"tags":[{"title":"new"}]}',
           (string) $this->createRowWithAssociatedData()
        );
    }

    public function testJsonSerializable()
    {
        $this->assertEquals(
            '{"title":"Article","author":{"name":"Jon"},"tags":[{"title":"new"}]}',
            json_encode($this->createRowWithAssociatedData())
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
        $this->assertEquals(
           json_decode('{"title":"Article","author":{"name":"Jon"},"tags":[{"title":"new"}]}', true),
            $this->createRowWithAssociatedData()->toArray()
        );
    }

    private function createRowWithAssociatedData(): Row
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

        return $row;
    }
}
