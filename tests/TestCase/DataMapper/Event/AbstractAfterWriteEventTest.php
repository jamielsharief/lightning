<?php declare(strict_types=1);

namespace Lightning\Test\DataMapper\Event;

use Lightning\Entity\Entity;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Entity\EntityInterface;

use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\TagsFixture;
use Lightning\QueryBuilder\QueryBuilder;
use Lightning\DataMapper\AbstractDataMapper;
use Lightning\DataMapper\Event\AfterSaveEvent;
use Lightning\DataMapper\Event\AfterCreateEvent;
use Lightning\DataMapper\Event\AfterDeleteEvent;
use Lightning\DataMapper\Event\AfterUpdateEvent;
use Lightning\DataMapper\DataSource\DatabaseDataSource;
use Lightning\DataMapper\Event\AbstractAfterWriteEvent;

class TagAfterWriteDataMapper extends AbstractDataMapper
{
    protected $primaryKey = 'id';
    protected string $table = 'tags';
    protected array $fields = [
        'id', 'name','created_at','updated_at'
    ];
}

final class AbstractAfterWriteEventTest extends TestCase
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
            [new AfterCreateEvent(new TagAfterWriteDataMapper($this->datasource), new Entity())],
            [new AfterUpdateEvent(new TagAfterWriteDataMapper($this->datasource), new Entity())],
            [new AfterSaveEvent(new TagAfterWriteDataMapper($this->datasource), new Entity())],
            [new AfterDeleteEvent(new TagAfterWriteDataMapper($this->datasource), new Entity())]
        ];
    }

    /**
     * @dataProvider eventProvider
     */
    public function testGetDataMapper(AbstractAfterWriteEvent $event): void
    {
        $this->assertInstanceOf(AbstractDataMapper::class, $event->getDataMapper());
    }

    /**
     * @dataProvider eventProvider
     */
    public function testGetEntity(AbstractAfterWriteEvent $event): void
    {
        $this->assertInstanceOf(EntityInterface::class, $event->getEntity());
    }
}
