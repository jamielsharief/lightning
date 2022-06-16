<?php declare(strict_types=1);

namespace Lightning\Test\DataMapper\Event;

use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Event\EventDispatcher;

use Lightning\Entity\EntityInterface;
use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\TagsFixture;
use Lightning\QueryBuilder\QueryBuilder;
use Lightning\DataMapper\AbstractDataMapper;
use Lightning\DataMapper\Event\BeforeSaveEvent;
use Lightning\DataMapper\Event\BeforeCreateEvent;
use Lightning\DataMapper\Event\BeforeDeleteEvent;
use Lightning\DataMapper\Event\BeforeUpdateEvent;
use Lightning\DataMapper\DataSource\DatabaseDataSource;
use Lightning\DataMapper\Event\AbstractBeforeWriteEvent;
use Lightning\Test\TestCase\DataMapper\Entity\TagEntity;

class TagWriteDataMapper extends AbstractDataMapper
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

final class AbstractBeforeWriteEventTest extends TestCase
{
    protected DatabaseDataSource $datasource;
    protected FixtureManager $fixtureManager;

    public function setUp(): void
    {
        $pdo = (new PdoFactory())->create(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));
        $this->fixtureManager = new FixtureManager($pdo);
        $this->fixtureManager->load([
            TagsFixture::class
        ]);
    }

    public function eventProvider(): array
    {
        $pdo = (new PdoFactory())->create(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));
        $this->datasource = new DatabaseDataSource($pdo, new QueryBuilder());

        return [
            [new BeforeCreateEvent(new TagWriteDataMapper($this->datasource, new EventDispatcher()), new TagEntity())],
            [new BeforeUpdateEvent(new TagWriteDataMapper($this->datasource, new EventDispatcher()), new TagEntity())],
            [new BeforeSaveEvent(new TagWriteDataMapper($this->datasource, new EventDispatcher()), new TagEntity())],
            [new BeforeDeleteEvent(new TagWriteDataMapper($this->datasource, new EventDispatcher()), new TagEntity())]
        ];
    }

    /**
     * @dataProvider eventProvider
     */
    public function testGetDataMapper(AbstractBeforeWriteEvent $event): void
    {
        $this->assertInstanceOf(AbstractDataMapper::class, $event->getDataMapper());
    }

    /**
     * @dataProvider eventProvider
     */
    public function testGetEntity(AbstractBeforeWriteEvent $event): void
    {
        $this->assertInstanceOf(EntityInterface::class, $event->getEntity());
    }

    /**
     * @dataProvider eventProvider
     */
    public function testIsStopped(AbstractBeforeWriteEvent $event): void
    {
        $this->assertFalse($event->isPropagationStopped());
        $this->assertInstanceOf(AbstractBeforeWriteEvent::class, $event->stop());
        $this->assertTrue($event->isPropagationStopped());
    }
}
