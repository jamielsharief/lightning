<?php declare(strict_types=1);

namespace Lightning\Test\DataMapper\Event;

use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\DataMapper\ResultSet;
use Lightning\DataMapper\QueryObject;

use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\TagsFixture;
use Lightning\QueryBuilder\QueryBuilder;
use Lightning\DataMapper\AbstractDataMapper;
use Lightning\DataMapper\Event\AfterFindEvent;
use Lightning\DataMapper\DataSource\DatabaseDataSource;

class TagAfterFindDataMapper extends AbstractDataMapper
{
    protected $primaryKey = 'id';
    protected string $table = 'tags';
    protected array $fields = [
        'id', 'name','created_at','updated_at'
    ];
}

final class AfterFindEventTest extends TestCase
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

        $this->datasource = new DatabaseDataSource($pdo, new QueryBuilder());
    }

    private function createAfterFindEvent(): AfterFindEvent
    {
        $dataMapper = new TagAfterFindDataMapper($this->datasource);
        $queryObject = new QueryObject(['foo' => 'bar']);
        $resultSet = new ResultSet([]);

        return new AfterFindEvent($dataMapper, $resultSet, $queryObject);
    }

    public function testGetDataMapper(): void
    {
        $this->assertInstanceOf(AbstractDataMapper::class, $this->createAfterFindEvent()->getDataMapper());
    }

    public function testGetQuery(): void
    {
        $this->assertInstanceOf(QueryObject::class, $this->createAfterFindEvent()->getQuery());
    }

    public function getResultSet(): void
    {
        $this->assertInstanceOf(ResultSet::class, $this->createAfterFindEvent()->getResultSet());
    }
}
