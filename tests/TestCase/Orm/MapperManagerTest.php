<?php declare(strict_types=1);

namespace Lightning\Test\Orm;

use PHPUnit\Framework\TestCase;
use Lightning\Orm\MapperManager;
use App\Controllers\EntityInterface;
use Lightning\Entity\AbstractEntity;
use Lightning\Event\EventDispatcher;
use Lightning\Event\ListenerRegistry;
use Lightning\DataMapper\DataSourceInterface;
use Lightning\Orm\AbstractObjectRelationalMapper;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\DataMapper\DataSource\MemoryDataSource;
use Lightning\Entity\EntityInterface as EntityEntityInterface;

class DummyArticleEntity extends AbstractEntity
{
    private ?int $id = null;
    private string $title;
    private string $body;
    private ?int $author_id = null;
    private ?string $created_at = null;
    private ?string $updated_at = null;
    private ?EntityInterface $author = null;

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

    public function getAuthor(): ?EntityInterface
    {
        return $this->author;
    }

    public function setAuthor(?EntityInterface $author): self
    {
        $this->author = $author;

        return $this;
    }
}

class DummyArticle extends AbstractObjectRelationalMapper
{
    protected string $table = 'articles';
    public function mapDataToEntity(array $state): EntityEntityInterface
    {
        return DummyArticleEntity::fromState($state);
    }
}

final class MapperManagerTest extends TestCase
{
    public function testGet(): void
    {
        $dataSource = new MemoryDataSource();
        $eventDispatcher = new EventDispatcher(new ListenerRegistry());
        $manager = new MapperManager($dataSource, $eventDispatcher);

        $this->assertInstanceOf(
            DummyArticle::class, $manager->get(DummyArticle::class)
        );
    }
    public function testAdd(): void
    {
        $dataSource = new MemoryDataSource();
        $eventDispatcher = new EventDispatcher(new ListenerRegistry());
        $manager = new MapperManager($dataSource, $eventDispatcher);

        $mapper = new DummyArticle($dataSource, $eventDispatcher, $manager);
        $this->assertInstanceOf(
          MapperManager::class, $manager->add($mapper)
        );
    }

    public function testConfigure(): void
    {
        $dataSource = new MemoryDataSource();
        $eventDispatcher = new EventDispatcher(new ListenerRegistry());
        $manager = new MapperManager($dataSource, $eventDispatcher);

        $this->assertInstanceOf(
            MapperManager::class, $manager->configure(DummyArticle::class, function (DataSourceInterface $dataSource, EventDispatcherInterface $eventDispatcher, MapperManager $manager) {
                $mapper = new DummyArticle($dataSource, $eventDispatcher, new MapperManager($dataSource, $eventDispatcher));
                $mapper->foo = 'bar'; // ensure its callback

                return $mapper;
            })
          );

        $this->assertEquals('bar', $manager->get(DummyArticle::class)->foo);
    }

    /**
     * @depends testAdd
     */
    public function testGetExisting(): void
    {
        $dataSource = new MemoryDataSource();
        $eventDispatcher = new EventDispatcher(new ListenerRegistry());
        $manager = new MapperManager($dataSource, $eventDispatcher);

        $mapper = new DummyArticle($dataSource, $eventDispatcher, $manager);

        $mapper->foo = 'bar'; // test its not being created

        $manager->add($mapper);

        $this->assertEquals(
          $mapper, $manager->get(DummyArticle::class)
        );
    }
}
