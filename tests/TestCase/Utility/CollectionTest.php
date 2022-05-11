<?php declare(strict_types=1);

namespace Lightning\Test\Utility;

use PHPUnit\Framework\TestCase;
use Lightning\Utility\Collection;

final class CollectionTest extends TestCase
{
    public function testCountable(): void
    {
        $this->assertCount(0, new Collection([]));
        $this->assertCount(3, new Collection([1,2,3]));
    }

    public function testIsEmpty(): void
    {
        $collection = new Collection([]);
        $this->assertTrue($collection->isEmpty());

        $collection = new Collection([1,2,3]);
        $this->assertFalse($collection->isEmpty());

        $collection->forEach(function (int $id) {
        });
    }

    public function testClear(): void
    {
        $this->markTestIncomplete();
    }

    public function testToArray(): void
    {
        $collection = new Collection([]);
        $this->assertEquals([], $collection->toArray());

        $collection = new Collection([1,2,3]);
        $this->assertEquals([1,2,3], $collection->toArray());
    }

    public function testToString(): void
    {
        $collection = new Collection([1,2,3]);
        $this->assertEquals('[1,2,3]', $collection->toString());
    }

    public function testStringable(): void
    {
        $collection = new Collection([1,2,3]);
        $this->assertEquals('[1,2,3]', (string) $collection);
    }

    public function testJsonSerializable(): void
    {
        $collection = new Collection([1,2,3]);
        $this->assertEquals('[1,2,3]', json_encode($collection));
    }

    public function testIteratorAggregate(): void
    {
        $collection = new Collection([1,2,3]);
        $this->assertEquals([1,2,3], iterator_to_array($collection));
    }

    public function testArrayAccess(): void
    {
        $collection = new Collection([]);

        $collection['key'] = 'value';
        $this->assertEquals('value', $collection['key']);
        $this->assertTrue(isset($collection['key']));
        unset($collection['key']);
        $this->assertFalse(isset($collection['key']));
    }

    public function testAdd(): void
    {
        $collection = new Collection();
        $this->assertCount(1, $collection->add('foo'));
    }

    public function testRemove(): void
    {
        $collection = new Collection(['foo']);
        $this->assertTrue($collection->remove('foo'));
        $this->assertFalse($collection->remove('foo'));
    }

    public function testSetGet(): void
    {
        $collection = new Collection();
        $this->assertEquals('foo', $collection->set(1, 'foo')->get(1));
        $this->assertNull($collection->get('foo'));
    }

    public function testIndexOf(): void
    {
        $collection = new Collection([1,2,3]);
        $this->assertNull($collection->indexOf('foo'));
        $this->assertEquals(0, $collection->indexOf(1));
        $this->assertEquals(1, $collection->indexOf(2));
    }

    public function testContains(): void
    {
        $collection = new Collection([1,2,3]);

        $this->assertTrue($collection->contains(1));
        $this->assertFalse($collection->contains(4));
    }

    public function testForEach(): void
    {
        $collection = new Collection([1,2,3]);
        $collection->forEach(function (int $id) {
            $this->assertTrue(in_array($id, [1,2,3]));
        });
    }

    public function testMap(): void
    {
        $collection = new Collection([1,2,3]);
        $collection = $collection->map(function (int $id) {
            return $id + 10;
        });

        $this->assertEquals([11,12,13], $collection->toArray());
    }

    public function testFilter(): void
    {
        $collection = new Collection([1,2,3,4,5,6,7,8,9,10]);
        $collection = $collection->filter(function (int $id) {
            return $id > 3 && $id < 8;
        });
        $this->assertEquals([3 => 4,4 => 5,5 => 6,6 => 7], $collection->toArray());
    }

    public function testExists(): void
    {
        $collection = new Collection([1,2,3]);

        $this->assertTrue($collection->exists(function (int $id) {
            return $id === 2;
        }));

        $this->assertFalse($collection->exists(function (int $id) {
            return $id === 10;
        }));
    }
}
