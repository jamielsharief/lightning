<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\DataMapper\DataSource;

use PHPUnit\Framework\TestCase;
use Lightning\DataMapper\QueryObject;
use Lightning\DataMapper\DataSource\MemoryDataSource;

final class MemoryDataSourceTest extends TestCase
{
    protected array $sampleData = [
        1000 => ['id' => 1000,'name' => 'foo'],
        1001 => ['id' => 1001,'name' => 'bar'],
        1003 => ['id' => 1003,'name' => 'foobar']
    ];

    public function testCreate(): void
    {
        $ds = new MemoryDataSource();
        $this->assertTrue($ds->create('test', ['name' => 'foo']));
        $this->assertEquals(1, $ds->getGeneratedId());
    }

    public function testCreateAutoIncrement(): void
    {
        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ], 1000);

        $this->assertTrue($ds->create('test', ['conditions' => ['name' => 'foo']]));
        $this->assertEquals(1002, $ds->getGeneratedId());

        $this->assertTrue($ds->create('test', ['conditions' => ['name' => 'foo']]));
        $this->assertEquals(1004, $ds->getGeneratedId());
    }

    public function testReadNoResults(): void
    {
        $ds = new MemoryDataSource();
        $this->assertEmpty($ds->read('test', new QueryObject(['id' => 1000])));
    }

    public function testRead(): void
    {
        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ]);

        $this->assertCount(3, $ds->read('test', new QueryObject()));
        $this->assertCount(1, $ds->read('test', new QueryObject(['id' => 1000])));
        $this->assertCount(2, $ds->read('test', new QueryObject(['id >' => 1000])));
    }

    public function testReadFields(): void
    {
        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ]);

        $result = $ds->read('test', new QueryObject(['id' => 1000]));
        $this->assertEquals(['id','name'], array_keys($result[0]));

        $result = $ds->read('test', new QueryObject(['id' => 1000], ['fields' => ['name']]));
        $this->assertEquals(['name'], array_keys($result[0]));
    }

    public function testReadOrder(): void
    {
        $ds = new MemoryDataSource([
            'test' => [
                ['name' => 'Becky'],
                ['name' => 'Alex'],
                ['name' => 'Debby'],
                ['name' => 'Claire']
            ]
        ]);

        $query = new QueryObject();
        $query->setOption('order', ['name' => 'ASC']);
        $result = $ds->read('test', $query);
        $this->assertEquals(
            ['Alex','Becky','Claire','Debby'],
            array_column($result, 'name')
        );
    }

    public function testReadOrderMulti(): void
    {
        $ds = new MemoryDataSource([
            'test' => [
                ['name' => 'Alex', 'surname' => 'Smith'],
                ['name' => 'Becky', 'surname' => 'Jones'],
                ['name' => 'Claire', 'surname' => 'Smith'],
                ['name' => 'Debby', 'surname' => 'Jones'],
            ]
        ]);

        $result = $ds->read('test', new QueryObject([], ['order' => ['surname' => 'ASC', 'name' => 'ASC']]));

        $this->assertEquals(
            ['Becky','Debby','Alex','Claire'],
            array_column($result, 'name')
        );
    }

    public function testReadLimit(): void
    {
        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ]);

        $query = new QueryObject();
        $query->setCriteria(['id' => 1000]);
        $query->setOption('limit', 2);

        $this->assertCount(1, $ds->read('test', $query));

        $query = new QueryObject();
        $this->assertCount(2, $ds->read('test', $query->setOption('limit', 2)));
        $this->assertCount(3, $ds->read('test', $query->setOption('limit', 3)));
        $this->assertCount(3, $ds->read('test', $query->setOption('limit', 4)));
    }

    public function testReadLimitOffset(): void
    {
        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ]);

        $this->assertCount(0, $ds->read('test', new QueryObject(['id' => 1000], ['limit' => 2,'offset' => 1])));
        $this->assertCount(1, $ds->read('test', new QueryObject(['id' => 1001], ['limit' => 2,'offset' => 1])));

        $this->assertCount(2, $ds->read('test', new QueryObject([], ['limit' => 2,'offset' => 1])));
        $this->assertCount(2, $ds->read('test', new QueryObject([], ['limit' => 3,'offset' => 1])));
        $this->assertCount(1, $ds->read('test', new QueryObject([], ['limit' => 4,'offset' => 2])));
    }

    public function testUpdate(): void
    {
        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ]);
        $this->assertEquals(3, $ds->update('test', new QueryObject(), ['name' => 'xxx']));
        $this->assertEquals('xxx', $ds->read('test', new QueryObject(['id' => 1000]))[0]['name']);
        $this->assertEquals('xxx', $ds->read('test', new QueryObject(['id' => 1001]))[0]['name']);
        $this->assertEquals('xxx', $ds->read('test', new QueryObject(['id' => 1003]))[0]['name']);
    }

    public function testUpdateLimit(): void
    {
        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ]);
        $this->assertEquals(1, $ds->update('test', new QueryObject([], ['limit' => 1]), ['name' => 'xxx']));
        $this->assertEquals('xxx', $ds->read('test', new QueryObject(['id' => 1000]))[0]['name']);
        $this->assertEquals('bar', $ds->read('test', new QueryObject(['id' => 1001]))[0]['name']);
        $this->assertEquals('foobar', $ds->read('test', new QueryObject(['id' => 1003]))[0]['name']);
    }

    public function testUpdateOffset(): void
    {
        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ]);
        $this->assertEquals(1, $ds->update('test', new QueryObject([], ['limit' => 1,'offset' => 1]), ['name' => 'xxx']));;
        $this->assertEquals('foo', $ds->read('test', new QueryObject(['id' => 1000]))[0]['name']);
        $this->assertEquals('xxx', $ds->read('test', new QueryObject(['id' => 1001]))[0]['name']);
        $this->assertEquals('foobar', $ds->read('test', new QueryObject(['id' => 1003]))[0]['name']);
    }

    public function testDelete(): void
    {
        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ]);
        $this->assertEquals(3, $ds->count('test', new QueryObject()));
        $this->assertEquals(3, $ds->delete('test', new QueryObject()));
        $this->assertEquals(0, $ds->count('test', new QueryObject()));
    }

    public function testDeleteLimit(): void
    {
        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ]);

        $this->assertEquals(1, $ds->delete('test', new QueryObject(['id >' => 1000], ['limit' => 1,'offset' => 1])));
        $this->assertEquals(2, $ds->count('test', new QueryObject()));
    }

    public function testDeleteLimitOffset(): void
    {
        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ]);

        $this->assertEquals(1, $ds->delete('test', new QueryObject(['id >' => 1000], ['limit' => 5,'offset' => 2])));

        $ds = new MemoryDataSource([
            'test' => $this->sampleData
        ]);

        $this->assertEquals(1, $ds->delete('test', new QueryObject(['id >' => 1000], ['limit' => 1,'offset' => 1])));
        $this->assertEquals(2, $ds->count('test', new QueryObject()));
    }
}
