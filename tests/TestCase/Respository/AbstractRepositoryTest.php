<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Repository;

use PDO;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Entity\AbstractEntity;
use Lightning\DataMapper\QueryObject;
use Lightning\Entity\EntityInterface;
use Lightning\Fixture\FixtureManager;
use Lightning\QueryBuilder\QueryBuilder;
use Lightning\Test\Fixture\ArticlesFixture;
use Lightning\DataMapper\AbstractDataMapper;
use Lightning\Repository\AbstractRepository;
use Lightning\DataMapper\DataSourceInterface;
use Lightning\DataMapper\DataSource\DatabaseDataSource;

class ArticleEntity extends AbstractEntity
{
    private ?int $id = null;
    private string $title;
    private string $body;
    private ?int $author_id = null;
    private ?string $created_at = null;
    private ?string $updated_at = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getAuthorId(): int
    {
        return $this->author_id;
    }

    public function setAuthorId(int $author_id): self
    {
        $this->author_id = $author_id;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?string $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}

class ArticleRepository extends AbstractRepository
{
}

class ArticleMapper extends AbstractDataMapper
{
    protected $primaryKey = 'id';
    protected string $table = 'articles';
    protected array $fields = ['id','title','body','author_id','created_at','updated_at'];

    public function mapDataToEntity(array $state): EntityInterface
    {
        return ArticleEntity::fromState($state);
    }
}

final class AbstractRepositoryTest extends TestCase
{
    protected PDO $pdo;
    protected FixtureManager $fixtureManager;

    public function setUp(): void
    {
        $pdoFactory = new PdoFactory();
        $this->pdo = $pdoFactory->create(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));

        $this->storage = new DatabaseDataSource($this->pdo, new QueryBuilder());

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([
            ArticlesFixture::class
        ]);
    }

    public function testFind(): void
    {
        $respository = $this->createRepository();

        $this->assertInstanceOf(EntityInterface::class, $respository->find());
    }

    public function testFindNone(): void
    {
        $respository = $this->createRepository();

        $this->assertNull($respository->find(new QueryObject(['id' => 1234])));
    }

    public function testFindAll(): void
    {
        $respository = $this->createRepository();

        $this->assertCount(3, $respository->findAll());
    }

    public function testFindAllNone(): void
    {
        $respository = $this->createRepository();

        $this->assertCount(0, $respository->findAll(new QueryObject(['id' => 1234])));
    }

    public function testFindCount(): void
    {
        $respository = $this->createRepository();

        $this->assertEquals(3, $respository->findCount());
    }

    public function testFindCountNone(): void
    {
        $respository = $this->createRepository();

        $this->assertEquals(0, $respository->findCount(new QueryObject(['id' => 1234])));
    }

    public function testFindList(): void
    {
        $respository = $this->createRepository();
        $expected = [
            0 => 1000,
            1 => 1001,
            2 => 1002
        ];

        $this->assertEquals($expected, $respository->findList());
    }

    public function testFindListWithOptions(): void
    {
        $respository = $this->createRepository();
        $expected = [
            1000 => 'Article #1',
            1001 => 'Article #2',
            1002 => 'Article #3'
        ];

        $this->assertEquals($expected, $respository->findList(null, ['keyField' => 'id','valueField' => 'title']));
    }

    public function testFindListNone(): void
    {
        $respository = $this->createRepository();

        $this->assertEquals([], $respository->findList(new QueryObject(['id' => 1234])));
    }

    public function testFindBy(): void
    {
        $respository = $this->createRepository();

        $this->assertNull($respository->findBy(['id' => 12345678]));
        $this->assertInstanceOf(EntityInterface::class, $respository->findBy(['id' => 1001]));
    }

    public function testFindAllBy(): void
    {
        $respository = $this->createRepository();

        $this->assertEmpty($respository->findAllBy(['id' => 12345678]));
        $this->assertCount(2, $respository->findAllBy(['id <>' => 1001]));
    }

    public function testFindCountBy(): void
    {
        $respository = $this->createRepository();

        $this->assertEquals(0, $respository->findCountBy(['id' => 12345678]));
        $this->assertEquals(2, $respository->findCountBy(['id <>' => 1001]));
    }

    public function testFindListBy(): void
    {
        $respository = $this->createRepository();

        $this->assertEmpty($respository->findListBy(['id' => 12345678]));
        $expected = [
            0 => 1000,
            1 => 1002
        ];
        $this->assertEquals($expected, $respository->findListBy(['id <>' => 1001]));
    }

    public function testSave(): void
    {
        $respository = $this->createRepository();
        $entity = $respository->find();
        $entity->setTitle('foo');

        $this->assertTrue($respository->save($entity));
    }

    public function testSaveMany(): void
    {
        $respository = $this->createRepository();
        $entities = $respository->findAll();
        array_walk($entities, function ($entity) {
            $entity->setTitle('foo');
        });
        $this->assertTrue($respository->saveMany($entities));
    }

    public function testDelete(): void
    {
        $respository = $this->createRepository();

        $this->assertTrue($respository->delete($respository->find()));
    }

    public function testDeleteMany(): void
    {
        $respository = $this->createRepository();

        $this->assertTrue($respository->deleteMany($respository->findAll()));
    }

    public function testDeleteAll(): void
    {
        $respository = $this->createRepository();
        $this->assertEquals(3, $respository->deleteAll(new QueryObject()));
    }

    public function testDeleteAllWithConditions(): void
    {
        $respository = $this->createRepository();
        $this->assertEquals(2, $respository->deleteAll(new QueryObject(['id <>' => 1001])));
    }

    public function testDeleteAllBy(): void
    {
        $respository = $this->createRepository();
        $this->assertEquals(2, $respository->deleteAllBy(['id <>' => 1001]));
    }

    public function testUpdateAll(): void
    {
        $respository = $this->createRepository();
        $this->assertEquals(3, $respository->updateAll(new QueryObject(), ['title' => 'foo']));
    }

    public function testUpdateAllWithConditions(): void
    {
        $respository = $this->createRepository();
        $this->assertEquals(2, $respository->updateAll(new QueryObject(['id <>' => 1001]), ['title' => 'foo']));
    }

    public function testUpdateAllBy(): void
    {
        $respository = $this->createRepository();
        $this->assertEquals(2, $respository->updateAllBy(['id <>' => 1001], ['title' => 'foo']));
    }

    public function testGetDataSource(): void
    {
        $this->assertInstanceOf(DataSourceInterface::class, $this->createRepository()->getDataSource());
    }

    public function testCreateQueryObject(): void
    {
        $respository = $this->createRepository();
        $query = $respository->createQueryObject(['foo' => 'bar'], ['bar' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $query->getCriteria());
        $this->assertEquals(['bar' => 'bar'], $query->getOptions());
    }

    public function createRepository(): ArticleRepository
    {
        $datasource = new DatabaseDataSource($this->pdo, new QueryBuilder());

        return new ArticleRepository(new ArticleMapper($datasource));
    }
}
