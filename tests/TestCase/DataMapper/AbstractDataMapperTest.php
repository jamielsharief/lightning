<?php declare(strict_types=1);

namespace Lightning\Test\DataMapper;

use PDO;
use ReflectionClass;
use BadMethodCallException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Entity\AbstractEntity;
use Lightning\Event\EventDispatcher;
use Lightning\DataMapper\QueryObject;
use Lightning\Entity\EntityInterface;

use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\TagsFixture;
use Lightning\QueryBuilder\QueryBuilder;
use Lightning\Test\Fixture\ArticlesFixture;
use Lightning\DataMapper\AbstractDataMapper;
use Lightning\TestSuite\TestEventDispatcher;
use Lightning\DataMapper\DataSourceInterface;
use Lightning\DataMapper\Event\AfterFindEvent;
use Lightning\DataMapper\Event\AfterSaveEvent;
use Lightning\DataMapper\Event\BeforeFindEvent;
use Lightning\DataMapper\Event\BeforeSaveEvent;
use Lightning\DataMapper\Event\InitializeEvent;
use Lightning\DataMapper\Event\AfterCreateEvent;
use Lightning\DataMapper\Event\AfterDeleteEvent;
use Lightning\DataMapper\Event\AfterUpdateEvent;
use Lightning\DataMapper\Event\BeforeCreateEvent;
use Lightning\DataMapper\Event\BeforeDeleteEvent;
use Lightning\DataMapper\Event\BeforeUpdateEvent;
use Lightning\TestSuite\EventDispatcherTestTrait;
use Lightning\DataMapper\DataSource\DatabaseDataSource;
use Lightning\Test\TestCase\DataMapper\Entity\TagEntity;
use Lightning\DataMapper\Exception\EntityNotFoundException;

final class ArticleEntity extends AbstractEntity
{
    private int $id;

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

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(string $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }
}

class Tag extends AbstractDataMapper
{
    protected $primaryKey = 'id';
    protected string $table = 'tags';
    protected array $fields = [
        'id', 'name','created_at','updated_at'
    ];

    public function mapDataToEntity(array $row): EntityInterface
    {
        return TagEntity::fromState($row);
    }
}

class Article extends AbstractDataMapper
{
    protected $primaryKey = 'id';
    protected string $table = 'articles';
    protected array $fields = [
        'id', 'title','body','author_id','created_at','updated_at'
    ];

    public function mapDataToEntity(array $row): EntityInterface
    {
        return ArticleEntity::fromState($row);
    }

    public function setProperty($property, $value)
    {
        $this->$property = $value;
    }

    public function getProperty($property)
    {
        return $this->$property;
    }

    public function hookFail()
    {
        return false;
    }
}

final class AbstractDataMapperTest extends TestCase
{
    use EventDispatcherTestTrait;

    protected PDO $pdo;
    protected FixtureManager $fixtureManager;

    public function setUp(): void
    {
        $pdoFactory = new PdoFactory();
        $this->pdo = $pdoFactory->create(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));

        $this->storage = new DatabaseDataSource($this->pdo, new QueryBuilder());

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([
            ArticlesFixture::class,
            TagsFixture::class,
        ]);

        $this->setEventDispatcher(new TestEventDispatcher(new EventDispatcher()));
    }

    public function testGetDataSource(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $this->assertInstanceOf(DataSourceInterface::class, $mapper->getDataSource());
    }

    public function testInitializeEventIsCalled(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->assertEventDispatched(InitializeEvent::class);
    }

    public function testCreateEntity(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $data = [
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $entity = $mapper->createEntity($data);
        $this->assertInstanceOf(EntityInterface::class, $entity);
        $this->assertSame($data, $entity->toState());
    }

    public function testCreateEntities(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $data = [
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $result = $mapper->createEntities([$data,$data]);
        $this->assertInstanceOf(EntityInterface::class, $result[0]);
        $this->assertInstanceOf(EntityInterface::class, $result[1]);
    }

    public function testGetPrimaryKey(): void
    {
        $this->assertEquals(['id'], (new Article($this->storage, $this->getEventDispatcher()))->getPrimaryKey());
    }

    public function testGet(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        /** @var ArticleEntity $article */
        $article = $mapper->getBy(['id' => 1000]);

        $this->assertInstanceOf(EntityInterface::class, $article);
        $this->assertSame('Article #1', $article->getTitle());
    }

    /**
     * Test that only selected fields are used
     */
    public function testGetFields(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $mapper->setProperty('fields', [
            'id', 'title','body','author_id'
        ]);
        /** @var ArticleEntity $article */
        $article = $mapper->getBy(['id' => 1000]);

        $this->assertNull($article->toState()['created_at']);
        $this->assertNull($article->toState()['updated_at']);
    }

    public function testGetNotFound(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Entity Not Found');

        /** @var ArticleEntity $article */
        $mapper->getBy(['id' => 1234]);
    }

    public function testFindCount(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->assertEquals(3, $mapper->findCount());
        $this->assertEventsDispatchedEquals([InitializeEvent::class,BeforeFindEvent::class]);
    }

    public function testFindCountWithQuery(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->assertEquals(1, $mapper->findCountBy(['id' => 1000]));
        $this->assertEquals(0, $mapper->findCountBy(['id' => 1234]));
    }

    public function testFind(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $entity = $mapper->find(new QueryObject());
        $this->assertEquals('Article #1', $entity->getTitle());
        $this->assertEventsDispatchedEquals([InitializeEvent::class,BeforeFindEvent::class,AfterFindEvent::class]);
    }

    public function testFindWithCondition(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $entity = $mapper->findBy(['id' => 1000]);
        $this->assertEquals('Article #1', $entity->getTitle());
    }

    public function testFindNoResult(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->assertNull($mapper->findBy(['id' => 1234]));
        $this->assertEventsDispatchedEquals([InitializeEvent::class,BeforeFindEvent::class]);
    }

    public function testFindAll(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $result = $mapper->findAll();
        $this->assertCount(3, $result);
    }

    public function testFindAllBy(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->assertCount(2, $mapper->findAllBy(['id !=' => 1000]));
    }

    public function testFindAllNoResults(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $items = $mapper->findAll();

        $this->assertEmpty($mapper->findAllBy(['id' => 123456789]));
    }

    public function testCreate(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $article = $mapper->createEntity([
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertTrue($mapper->save($article));

        //
        $expected = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql' ? 1 : 1003;
        $this->assertEquals($expected, $article->getId());

        $this->assertEventsDispatchedEquals([InitializeEvent::class,BeforeSaveEvent::class, BeforeCreateEvent::class,AfterCreateEvent::class,AfterSaveEvent::class]);
    }

    public function testUpdate(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $article = ArticleEntity::fromState([
            'id' => 1000,
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $article->markPersisted(true);

        $this->assertTrue($mapper->save($article));
        $this->assertEventsDispatchedEquals([InitializeEvent::class,BeforeSaveEvent::class, BeforeUpdateEvent::class,AfterUpdateEvent::class,AfterSaveEvent::class]);
    }

    public function testUpdateWithNoPrimaryKey(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Primary key `id` has no value');

        $entity = ArticleEntity::fromState([
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $entity->markPersisted(true);
        $mapper->save($entity);
    }

    public function testUpdateFail(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $article = ArticleEntity::fromState([
            'id' => 1234,
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $article->markPersisted(true);
        $this->assertFalse($mapper->save($article));
        $this->assertEventsDispatchedEquals([InitializeEvent::class,BeforeSaveEvent::class, BeforeUpdateEvent::class]);
    }

    public function testSaveMany(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $entities = $mapper->findAll();
        foreach ($entities as $entity) {
            $entity->setUpdatedAt(date('Y-m-d H:i:s'));
        }
        $this->assertTrue($mapper->saveMany($entities));
    }

    public function testSaveManyFail(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $article = ArticleEntity::fromState([
            'id' => 1234,
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $article->markPersisted(true);
        $this->assertFalse($mapper->save($article));

        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $entities = $mapper->createCollection([$article]);
        $this->assertFalse($mapper->saveMany($entities));
    }

    public function testUpdateAll(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $this->assertEquals(2, $mapper->updateAll(new QueryObject(['id !=' => 1001]), ['author_id' => 1111]));
        $this->assertEquals(0, $mapper->updateAll(new QueryObject(['id' => 1234]), ['author_id' => 1111]));
    }

    public function testUpdateAllException(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data cannot be empty');

        $mapper->updateAll(new QueryObject(), []);
    }

    public function testUpdateAllBy(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $this->assertEquals(2, $mapper->updateAllBy(['id !=' => 1001], ['author_id' => 1111]));
    }

    public function testDeleteAll(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->assertEquals(2, $mapper->deleteAll(new QueryObject(['id !=' => 1001])));
        $this->assertEquals(0, $mapper->deleteAll(new QueryObject(['id' => 1234])));
    }

    public function testDeleteAllBy(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->assertEquals(2, $mapper->deleteAllBy(['id !=' => 1001]));
    }

    public function testDelete(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $article = ArticleEntity::fromState([
            'id' => 1000,
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $article->markPersisted(true);

        $this->assertTrue($mapper->delete($article));
        $this->assertEventsDispatchedEquals([InitializeEvent::class,BeforeDeleteEvent::class,AfterDeleteEvent::class]);
    }

    public function testDeleteFail(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $article = ArticleEntity::fromState([
            'id' => 1234,
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertFalse($mapper->delete($article));
        $this->assertEventsDispatchedEquals([InitializeEvent::class,BeforeDeleteEvent::class]);
    }

    public function testDeleteMany(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $articles = $mapper->findAll();

        $this->assertTrue($mapper->deleteMany($articles));
        $this->assertCount(0, $mapper->findAll());
    }

    public function testDeleteManyFail(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $article = ArticleEntity::fromState([
            'id' => 1234,
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $articles = [
            $mapper->find(),
            $article
        ];

        $this->assertFalse($mapper->deleteMany($articles));
        $this->assertCount(2, $mapper->findAll());
    }

    public function testFindList(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->assertEquals(
            [1000,1001,1002],
            $mapper->findList()
        );
    }

    public function testFindListWithNoPrimaryKey(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $reflection = new ReflectionClass($mapper);
        $property = $reflection->getProperty('primaryKey');
        $property->setAccessible(true);
        $property->setValue($mapper, ['article_id','author_id']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine primary key');
        $mapper->findList();
    }

    public function testFindListWithQuery(): void
    {
        $query = new QueryObject(['id !=' => 1001]);
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->assertEquals(
            [1000,1002],
            $mapper->findList($query)
        );
    }

    public function testFindListBy(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->assertEquals(
            [1000,1002],
            $mapper->findListBy(['id !=' => 1001])
        );
    }

    public function testFindListWithValues(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());
        $this->assertEquals(
            [1000 => 'Article #1',1001 => 'Article #2',1002 => 'Article #3'],
            $mapper->findList(null, ['idField' => 'id','valueField' => 'title'])
        );
    }

    public function testFindListGrouped(): void
    {
        $mapper = new Article($this->storage, $this->getEventDispatcher());

        $mapper->updateAll(new QueryObject(), ['author_id' => 2000]);
        $mapper->updateAll(new QueryObject(['id !=' => 1001]), ['author_id' => 4000]);

        $expected = [
            4000 => [
                1000 => 'Article #1',
                1002 => 'Article #3'
            ],
            2000 => [
                1001 => 'Article #2'
            ]
        ];

        $this->assertEquals(
           $expected, $mapper->findList(null, ['valueField' => 'title','groupField' => 'author_id'])
        );
    }

    // public function testBeforeFindHookFail(): void
    // {
    //     $mapper = new Article($this->storage, $this->getEventDispatcher());

    //     $this->assertNull($mapper->find());
    //     $this->assertTrue($mapper->findAll()->isEmpty());
    //     $this->assertEquals([], $mapper->findList());
    //     $this->assertEquals(0, $mapper->findCount());
    // }

    // public function testBeforeCreateHookFail(): void
    // {
    //     $mapper = new Article($this->storage, $this->getEventDispatcher());

    //     $article = $mapper->createEntity([
    //         'title' => 'test',
    //         'body' => 'none',
    //         'author_id' => 1234,
    //         'created_at' => date('Y-m-d H:i:s'),
    //         'updated_at' => date('Y-m-d H:i:s'),
    //     ]);

    //     $this->assertFalse($mapper->save($article));
    // }

    // public function testBeforeSaveHookFail(): void
    // {
    //     $mapper = new Article($this->storage, $this->getEventDispatcher());

    //     $article = $mapper->createEntity([
    //         'title' => 'test',
    //         'body' => 'none',
    //         'author_id' => 1234,
    //         'created_at' => date('Y-m-d H:i:s'),
    //         'updated_at' => date('Y-m-d H:i:s'),
    //     ]);

    //     $this->assertFalse($mapper->save($article));
    // }

    // public function testBeforeDeleteHookFail(): void
    // {
    //     $mapper = new Article($this->storage, $this->getEventDispatcher());
    //     $mapper->registerHook('beforeDelete', 'hookFail');

    //     $article = $mapper->find();

    //     $this->assertFalse($mapper->delete($article));
    // }

    // public function testBeforeUpdateHookFail(): void
    // {
    //     $mapper = new Article($this->storage, $this->getEventDispatcher());
    //     $mapper->registerHook('beforeUpdate', 'hookFail');

    //     $article = $mapper->find();

    //     $this->assertFalse($mapper->update($article));
    // }
}
