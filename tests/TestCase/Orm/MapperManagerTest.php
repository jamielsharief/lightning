<?php declare(strict_types=1);

namespace Lightning\Test\Orm;

use PHPUnit\Framework\TestCase;
use Lightning\Orm\MapperManager;
use Lightning\DataMapper\DataSourceInterface;
use Lightning\Orm\AbstractObjectRelationalMapper;
use Lightning\DataMapper\DataSource\MemoryDataSource;

class DummyArticle extends AbstractObjectRelationalMapper
{
    protected string $table = 'articles';
}

final class MapperManagerTest extends TestCase
{
    public function testGet(): void
    {
        $dataSource = new MemoryDataSource();
        $manager = new MapperManager($dataSource);

        $this->assertInstanceOf(
            DummyArticle::class, $manager->get(DummyArticle::class)
        );
    }
    public function testAdd(): void
    {
        $dataSource = new MemoryDataSource();
        $manager = new MapperManager($dataSource);

        $mapper = new DummyArticle($dataSource, new MapperManager($dataSource));
        $this->assertInstanceOf(
          MapperManager::class, $manager->add($mapper)
        );
    }

    public function testConfigure(): void
    {
        $dataSource = new MemoryDataSource();
        $manager = new MapperManager($dataSource);

        $this->assertInstanceOf(
            MapperManager::class, $manager->configure(DummyArticle::class, function (DataSourceInterface $dataSource, MapperManager $manager) {
                $mapper = new DummyArticle($dataSource, new MapperManager($dataSource));
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
        $manager = new MapperManager($dataSource);
        $mapper = new DummyArticle($dataSource, new MapperManager($dataSource));

        $mapper->foo = 'bar'; // test its not being created

        $manager->add($mapper);

        $this->assertEquals(
          $mapper, $manager->get(DummyArticle::class)
        );
    }
}
