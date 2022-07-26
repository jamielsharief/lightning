<?php declare(strict_types=1);

namespace Lightning\Test\Query;

use PDO;
use ArrayIterator;
use Lightning\Query\Query;
use BadMethodCallException;
use Lightning\Database\Row;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\TagsFixture;
use Lightning\QueryBuilder\QueryBuilder;
use Lightning\Test\Fixture\PostsFixture;
use Lightning\Test\Fixture\AuthorsFixture;
use Lightning\Test\Fixture\ArticlesFixture;

use Lightning\Test\Fixture\PostsTagsFixture;

final class QueryTest extends TestCase
{
    protected PDO $pdo;
    protected FixtureManager $fixtureManager;

    protected function setUp(): void
    {
        // Create Connection
        $pdoFactory = new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),true);
        $this->pdo = $pdoFactory->create();

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([
            ArticlesFixture::class,
            AuthorsFixture::class,
            PostsFixture::class,
            TagsFixture::class,
            PostsTagsFixture::class
        ]);
    }

    private function createQuery(): Query
    {
        return new Query($this->pdo, new QueryBuilder());
    }

    public function testSelectNoTableExecute()
    {
        $query = $this->createQuery();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Table for the query was not set');

        (string) $query->select(['*']);
    }

    public function testSelectFrom(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT * FROM articles',
            (string) $query
                ->select(['*'])
                ->from('articles')
        );
    }

    public function testSelectWhere(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT * FROM articles WHERE articles.id = :v0',
            (string) $query
                ->select(['*'])
                ->from('articles')
                ->where(['id' => 1234])
        );
    }

    public function testSelectLimit(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT * FROM articles WHERE articles.id = :v0 LIMIT 10',
            (string) $query
                ->select(['*'])
                ->from('articles')
                ->where(['id' => 1234])
                ->limit(10)
        );
    }

    public function testSelectLimitOffset(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT * FROM articles WHERE articles.id = :v0 LIMIT 10 OFFSET 100',
            (string) $query
                ->select(['*'])
                ->from('articles')
                ->where(['id' => 1234])
                ->limit(10, 100)
        );
    }

    public function testSelectLeftJoin(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT articles.id, authors.id FROM articles LEFT JOIN authors AS a ON articles.author_id = a.id',
            (string) $query
                ->select(['articles.id','authors.id'])
                ->from('articles')
                ->leftJoin('authors', 'a', ['articles.author_id = a.id'])
        );
    }

    public function testSelectRightJoin(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT articles.id, authors.id FROM articles RIGHT JOIN authors AS a ON articles.author_id = a.id',
            (string) $query
                ->select(['articles.id','authors.id'])
                ->from('articles')
                ->rightJoin('authors', 'a', ['articles.author_id = a.id'])
        );
    }

    public function testSelectInnerJoin(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT articles.id, authors.id FROM articles INNER JOIN authors AS a ON articles.author_id = a.id',
            (string) $query
                ->select(['articles.id','authors.id'])
                ->from('articles')
                ->innerJoin('authors', 'a', ['articles.author_id = a.id'])
        );
    }

    public function testSelectFullJoin(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT articles.id, authors.id FROM articles FULL JOIN authors AS a ON articles.author_id = a.id',
            (string) $query
                ->select(['articles.id','authors.id'])
                ->from('articles')
                ->fullJoin('authors', 'a', ['articles.author_id = a.id'])
        );
    }

    public function testSelectGroupBy(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT COUNT(*) AS count, articles.category FROM articles GROUP BY articles.category',
            (string) $query
                ->select(['COUNT(*) AS count','category'])
                ->from('articles')
                ->groupBy('category')
        );
    }

    public function testSelectOrderBy(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT * FROM articles ORDER BY articles.id DESC',
            (string) $query
                ->select(['*'])
                ->from('articles')
                ->orderBy('id DESC')
        );

        $query = $this->createQuery();
        $this->assertSame(
            'SELECT * FROM articles ORDER BY articles.id DESC',
            (string) $query
                ->select(['*'])
                ->from('articles')
                ->orderBy(['id' => 'DESC'])
        );
    }

    public function testSelectHaving(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT COUNT(id) AS count FROM articles HAVING count > 1',
            (string) $query
                ->select(['COUNT(id) AS count'])
                ->from('articles')
                ->having('count > 1')
        );
    }

    public function testSelectPaging(): void
    {
        $query = $this->createQuery();
        $this->assertSame(
            'SELECT * FROM articles LIMIT 20',
            (string) $query
                ->select(['*'])
                ->from('articles')
                ->page(1, 20)
        );

        $query = $this->createQuery();
        $this->assertSame(
            'SELECT * FROM articles LIMIT 20 OFFSET 80',
            (string) $query
                ->select(['*'])
                ->from('articles')
                ->page(5, 20)
        );
    }

    public function testFirst()
    {
        $query = $this->createQuery();

        $entity = $query
            ->select(['*'])
            ->from('articles')
            ->get();

        $this->assertInstanceOf(Row::class, $entity);

        $expected = [
            'id' => 1000,
            'title' => 'Article #1',
            'body' => 'A description for article #1',
            'author_id' => 2000,
            'created_at' => '2021-10-03 09:01:00',
            'updated_at' => '2021-10-03 09:02:00'
        ];
        $this->assertEquals($expected, $entity->toArray());
    }

    public function testFirstNull()
    {
        $query = $this->createQuery();

        $query
            ->select(['*'])
            ->from('articles')
            ->where(['id' => 1234]);

        $this->assertNull($query->get());
    }

    public function testGet()
    {
        $query = $this->createQuery();

        $collection = $query
            ->select(['*'])
            ->from('articles')
            ->all();

        $this->assertCount(3, $collection);
    }

    public function testTableGrouping()
    {
        $query = $this->createQuery();

        $result = $query
            ->select(['articles.id','articles.title','articles.author_id','authors.id','authors.name'])
            ->from('articles')
            ->leftJoin('authors', 'authors', ['articles.author_id = authors.id'])
            ->get();

        $this->assertInstanceOf(Row::class, $result->authors);
        $this->assertEquals('Jon', $result->authors->name);
    }

    public function testInsert(): void
    {
        $query = $this->createQuery();

        $query->insertInto('posts')
            ->values([
                'title' => 'This is an article',
                'body' => 'ss',
                'created_at' => '2021-10-23 15:35:00',
                'updated_at' => '2021-10-23 15:35:00',
            ]);

        $this->assertSame(
            'INSERT INTO posts (title, body, created_at, updated_at) VALUES (:v0, :v1, :v2, :v3)',
            (string) $query
        );

        $this->assertEquals(1, $query->execute());
    }

    public function testGetLastInsertId(): void
    {
        $query = $this->createQuery();

        $query->insertInto('posts')
            ->values([
                'id' => 12345678,
                'title' => 'This is an article',
                'body' => 'ss',
                'created_at' => '2021-10-23 15:35:00',
                'updated_at' => '2021-10-23 15:35:00',
            ]);

        $this->assertEquals(1, $query->execute());

        $this->assertIsString($query->getLastInsertId());
    }

    public function testValuesException(): void
    {
        $query = $this->createQuery();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('InsertInto must be called first');

        $query->values([
            'a' => 'b'
        ]);
    }

    public function testUpdate(): void
    {
        $query = $this->createQuery();

        $query->update('posts')
            ->set([
                'title' => 'foo'
            ])
            ->where(['id' => 1000]);

        $this->assertSame(
                'UPDATE posts SET title = :v0 WHERE posts.id = :v1',
                (string) $query
            );

        $this->assertEquals(1, $query->execute());
    }

    public function testUpdateNoRecords(): void
    {
        $query = $this->createQuery();

        $query->update('posts')
            ->set([
                'title' => 'foo'
            ])
            ->where(['id' => 10001]);

        $this->assertEquals(0, $query->execute());
    }

    public function testDeleteFrom(): void
    {
        $query = $this->createQuery();

        $query->deleteFrom('posts')
            ->where(['id' => 1000]);

        $this->assertSame(
                'DELETE FROM posts WHERE posts.id = :v0',
                (string) $query
            );

        $this->assertEquals(1, $query->execute());
    }

    public function testDeleteFromExecute(): void
    {
        $query = $this->createQuery();

        $query->deleteFrom('posts')
            ->where(['id' => 1000]);

        $this->assertEquals(1, $query->execute());
    }

    public function testDeleteFromExecuteNoRecords(): void
    {
        $query = $this->createQuery();

        $query->deleteFrom('posts')
            ->where(['id' => 10001]);

        $this->assertEquals(0, $query->execute());
    }

    public function testGetIterator(): void
    {
        $query = $this->createQuery();
        $query->select('*')->from('posts');

        $this->assertInstanceOf(ArrayIterator::class, $query->getIterator());
    }

    public function testGetPDO(): void
    {
        $this->assertInstanceOf(PDO::class, $this->createQuery()->getPdo());
    }
}
