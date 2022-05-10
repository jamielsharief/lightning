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
use Lightning\DataMapper\QueryObject;
use Lightning\Entity\EntityInterface;
use Lightning\Fixture\FixtureManager;

use Lightning\Test\Fixture\TagsFixture;
use Lightning\QueryBuilder\QueryBuilder;
use Lightning\Test\Fixture\ArticlesFixture;
use Lightning\DataMapper\AbstractDataMapper;
use Lightning\TestSuite\TestEventDispatcher;
use Lightning\DataMapper\DataSourceInterface;
use Lightning\Entity\Callback\AfterLoadInterface;
use Lightning\Entity\Callback\AfterSaveInterface;
use Lightning\TestSuite\EventDispatcherTestTrait;
use Lightning\Entity\Callback\BeforeSaveInterface;
use Lightning\Entity\Callback\AfterCreateInterface;
use Lightning\Entity\Callback\AfterDeleteInterface;
use Lightning\Entity\Callback\AfterUpdateInterface;
use Lightning\Entity\Callback\BeforeCreateInterface;
use Lightning\Entity\Callback\BeforeDeleteInterface;
use Lightning\Entity\Callback\BeforeUpdateInterface;
use Lightning\DataMapper\DataSource\DatabaseDataSource;
use Lightning\Test\TestCase\DataMapper\Entity\TagEntity;
use Lightning\DataMapper\Exception\EntityNotFoundException;

final class ArticleEntity extends AbstractEntity implements BeforeSaveInterface, BeforeCreateInterface, BeforeUpdateInterface, BeforeDeleteInterface, AfterSaveInterface, AfterCreateInterface, AfterUpdateInterface, AfterLoadInterface, AfterDeleteInterface
{
    private int $id;

    private string $title;
    private string $body;
    private ?int $author_id = null;
    private ?string $created_at = null;
    private ?string $updated_at = null;

    protected array $callbacks = [];

    public function beforeCreate(): void
    {
        $this->callbacks[] = 'beforeCreate';
    }

    public function beforeUpdate(): void
    {
        $this->callbacks[] = 'beforeUpdate';
    }

    public function beforeSave(): void
    {
        $this->callbacks[] = 'beforeSave';
    }

    public function beforeDelete(): void
    {
        $this->callbacks[] = 'beforeDelete';
    }

    public function afterCreate(): void
    {
        $this->callbacks[] = 'afterCreate';
    }

    public function afterUpdate(): void
    {
        $this->callbacks[] = 'afterUpdate';
    }

    public function afterSave(): void
    {
        $this->callbacks[] = 'afterSave';
    }

    public function afterDelete(): void
    {
        $this->callbacks[] = 'afterDelete';
    }

    public function afterLoad(): void
    {
        $this->callbacks[] = 'afterLoad';
    }

    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    public function didCall(string $callback): bool
    {
        return in_array($callback, $this->callbacks);
    }

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

/**
 * @method ?ArticleEntity find(QueryObject $query)
 * @method ArticleEntity[] findAll(QueryObject $query)
 * @method ?ArticleEntity findBy(array $criteria =[], array $options = [])
 * @method ArticleEntity[] findAllby(array $criteria =[], array $options = [])
 */
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

        $this->setEventDispatcher(new TestEventDispatcher());
    }

    public function testGetDataSource(): void
    {
        $mapper = new Article($this->storage);

        $this->assertInstanceOf(DataSourceInterface::class, $mapper->getDataSource());
    }

    public function testCreateEntity(): void
    {
        $mapper = new Article($this->storage);

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
        $mapper = new Article($this->storage);

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
        $this->assertEquals(['id'], (new Article($this->storage))->getPrimaryKey());
    }

    public function testGet(): void
    {
        $mapper = new Article($this->storage);

        /** @var ArticleEntity $article */
        $article = $mapper->getBy(['id' => 1000]);

        $this->assertInstanceOf(EntityInterface::class, $article);
        $this->assertSame('Article #1', $article->getTitle());
    }

    /**
     * Test that only selected fields are used
     *
     * @return void
     */
    public function testGetFields(): void
    {
        $mapper = new Article($this->storage);
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
        $mapper = new Article($this->storage);

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Entity Not Found');

        /** @var ArticleEntity $article */
        $mapper->getBy(['id' => 1234]);
    }

    public function testFindCount(): void
    {
        $mapper = new Article($this->storage);
        $this->assertEquals(3, $mapper->findCount());
    }

    public function testFindCountWithQuery(): void
    {
        $mapper = new Article($this->storage);
        $this->assertEquals(1, $mapper->findCountBy(['id' => 1000]));
        $this->assertEquals(0, $mapper->findCountBy(['id' => 1234]));
    }

    public function testFind(): void
    {
        $mapper = new Article($this->storage);
        $entity = $mapper->find(new QueryObject());
        $this->assertEquals('Article #1', $entity->getTitle());
        $this->assertEquals(['afterLoad'], $entity->getCallbacks());
    }

    public function testFindWithCondition(): void
    {
        $mapper = new Article($this->storage);
        $entity = $mapper->findBy(['id' => 1000]);
        $this->assertEquals('Article #1', $entity->getTitle());
    }

    public function testFindNoResult(): void
    {
        $mapper = new Article($this->storage);
        $this->assertNull($mapper->findBy(['id' => 1234]));
    }

    public function testFindAll(): void
    {
        $mapper = new Article($this->storage);
        $result = $mapper->findAll();
        $this->assertCount(3, $result);

        $this->assertEquals(['afterLoad'], $result[1]->getCallbacks());
    }

    public function testFindAllBy(): void
    {
        $mapper = new Article($this->storage);
        $this->assertCount(2, $mapper->findAllBy(['id !=' => 1000]));
    }

    public function testFindAllNoResults(): void
    {
        $mapper = new Article($this->storage);
        $items = $mapper->findAll();

        $this->assertEmpty($mapper->findAllBy(['id' => 123456789]));
    }

    public function testCreate(): void
    {
        $mapper = new Article($this->storage);

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

        $this->assertEquals(['beforeSave','beforeCreate','afterCreate','afterSave'], $article->getCallbacks());
    }

    public function testUpdate(): void
    {
        $mapper = new Article($this->storage);

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
        $this->assertEquals(['beforeSave','beforeUpdate','afterUpdate','afterSave'], $article->getCallbacks());
    }

    public function testUpdateWithNoPrimaryKey(): void
    {
        $mapper = new Article($this->storage);

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
        $mapper = new Article($this->storage);

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
        $this->assertEquals(['beforeSave','beforeUpdate'], $article->getCallbacks());
    }

    public function testSaveMany(): void
    {
        $mapper = new Article($this->storage);
        $entities = $mapper->findAll();
        foreach ($entities as $entity) {
            $entity->setUpdatedAt(date('Y-m-d H:i:s'));
        }
        $this->assertTrue($mapper->saveMany($entities));
    }

    /**
     * @depends testBeforeSaveHookFail
     */
    public function testSaveManyFail(): void
    {
        $mapper = new Article($this->storage);
        $mapper->registerHook('beforeSave', 'hookFail');;
        $entities = $mapper->findAll();
        $this->assertFalse($mapper->saveMany($entities));
    }

    public function testUpdateAll(): void
    {
        $mapper = new Article($this->storage);

        $this->assertEquals(2, $mapper->updateAll(new QueryObject(['id !=' => 1001]), ['author_id' => 1111]));
        $this->assertEquals(0, $mapper->updateAll(new QueryObject(['id' => 1234]), ['author_id' => 1111]));
    }

    public function testUpdateAllException(): void
    {
        $mapper = new Article($this->storage);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data cannot be empty');

        $mapper->updateAll(new QueryObject(), []);
    }

    public function testUpdateAllBy(): void
    {
        $mapper = new Article($this->storage);

        $this->assertEquals(2, $mapper->updateAllBy(['id !=' => 1001], ['author_id' => 1111]));
    }

    public function testDeleteAll(): void
    {
        $mapper = new Article($this->storage);
        $this->assertEquals(2, $mapper->deleteAll(new QueryObject(['id !=' => 1001])));
        $this->assertEquals(0, $mapper->deleteAll(new QueryObject(['id' => 1234])));
    }

    public function testDeleteAllBy(): void
    {
        $mapper = new Article($this->storage);
        $this->assertEquals(2, $mapper->deleteAllBy(['id !=' => 1001]));
    }

    public function testDelete(): void
    {
        $mapper = new Article($this->storage);

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

        $this->assertEquals(['beforeDelete','afterDelete'], $article->getCallbacks());
    }

    public function testDeleteFail(): void
    {
        $mapper = new Article($this->storage);
        $article = ArticleEntity::fromState([
            'id' => 1234,
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertFalse($mapper->delete($article));
        $this->assertEquals(['beforeDelete'], $article->getCallbacks());
    }

    public function testDeleteMany(): void
    {
        $mapper = new Article($this->storage);
        $articles = $mapper->findAll();

        $this->assertTrue($mapper->deleteMany($articles));
        $this->assertCount(0, $mapper->findAll());
    }

    public function testDeleteManyFail(): void
    {
        $mapper = new Article($this->storage);
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
        $mapper = new Article($this->storage);
        $this->assertEquals(
            [1000,1001,1002],
            $mapper->findList()
        );
    }

    public function testFindListWithNoPrimaryKey(): void
    {
        $mapper = new Article($this->storage);
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
        $mapper = new Article($this->storage);
        $this->assertEquals(
            [1000,1002],
            $mapper->findList($query)
        );
    }

    public function testFindListBy(): void
    {
        $mapper = new Article($this->storage);
        $this->assertEquals(
            [1000,1002],
            $mapper->findListBy(['id !=' => 1001])
        );
    }

    public function testFindListWithValues(): void
    {
        $mapper = new Article($this->storage);
        $this->assertEquals(
            [1000 => 'Article #1',1001 => 'Article #2',1002 => 'Article #3'],
            $mapper->findList(null, ['idField' => 'id','valueField' => 'title'])
        );
    }

    public function testFindListGrouped(): void
    {
        $mapper = new Article($this->storage);

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

    public function testBeforeFindHookFail(): void
    {
        $mapper = new Article($this->storage);
        $mapper->registerHook('beforeFind', 'hookFail');

        $this->assertNull($mapper->find());
        $this->assertEquals([], $mapper->findAll());
        $this->assertEquals([], $mapper->findList());
        $this->assertEquals(0, $mapper->findCount());
    }

    public function testBeforeCreateHookFail(): void
    {
        $mapper = new Article($this->storage);
        $mapper->registerHook('beforeCreate', 'hookFail');

        $article = $mapper->createEntity([
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertFalse($mapper->save($article));
    }

    public function testBeforeSaveHookFail(): void
    {
        $mapper = new Article($this->storage);
        $mapper->registerHook('beforeSave', 'hookFail');

        $article = $mapper->createEntity([
            'title' => 'test',
            'body' => 'none',
            'author_id' => 1234,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertFalse($mapper->save($article));
    }

    public function testBeforeDeleteHookFail(): void
    {
        $mapper = new Article($this->storage);
        $mapper->registerHook('beforeDelete', 'hookFail');

        $article = $mapper->find();

        $this->assertFalse($mapper->delete($article));
    }

    public function testBeforeUpdateHookFail(): void
    {
        $mapper = new Article($this->storage);
        $mapper->registerHook('beforeUpdate', 'hookFail');

        $article = $mapper->find();

        $this->assertFalse($mapper->update($article));
    }
}
