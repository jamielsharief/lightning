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
    }

    public function testClear(): void
    {
        $collection = new Collection([1,2,3]);
        $this->assertTrue($collection->clear()->isEmpty());
    }

    public function testToArray(): void
    {
        $collection = new Collection([]);
        $this->assertEquals([], $collection->toArray());

        $collection = new Collection([1,2,3]);
        $this->assertEquals([1,2,3], $collection->toArray());
    }

    public function testKeys(): void
    {
        $collection = new Collection(['foo' => 1,'bar' => 2,'foobar' => 3]);
        $this->assertEquals(
            ['foo','bar','foobar'],
            $collection->keys()
        );
    }

    public function testValues(): void
    {
        $collection = new Collection([1 => 'foo',2 => 'bar',3 => 'foobar']);
        $this->assertEquals(
            ['foo','bar','foobar'],
            $collection->values()
        );
    }

    public function testChunk(): void
    {
        $this->assertIsArray((new Collection())->chunk(5));
        $collection = new Collection([1 => 'foo',2 => 'bar',3 => 'foobar',4 => 'barfoo']);

        $chunks = $collection->chunk(3, true);
        $this->assertCount(2, $chunks);
        $this->assertInstanceOf(Collection::class, $chunks[0]);
        $this->assertEquals('{"1":"foo","2":"bar","3":"foobar"}', (string) $chunks[0]);
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

    public function testGet(): void
    {;
        $this->assertNull((new Collection())->get(0));

        $collection = new Collection(['foo']);

        $this->assertEquals('foo', $collection->get(0));
        $this->assertEquals('foo', $collection->get());

        $collection = new Collection(['a' => 10,'b' => 5,'c' => 20]);
        $this->assertEquals(5, $collection->get('b'));
    }

    public function testSet(): void
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

    public function testUnset(): void
    {
        $collection = new Collection([1,2,3]);
        $this->assertCount(2, $collection->unset(2));
        $this->assertCount(2, $collection->unset(5));
    }

    public function testHas(): void
    {
        $collection = new Collection(['a' => 1,'b' => 2,'c' => 3]);

        $this->assertTrue($collection->keyExists('a'));
        $this->assertFalse($collection->keyExists(2));
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
        $collection->each(function (int $id) {
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

    public function testReduce(): void
    {
        $collection = new Collection([1,2,3]);
        $result = $collection->reduce(function ($accumulated, $value) {
            return $accumulated + $value;
        });
        $this->assertEquals(6, $result);
    }

    public function testSort(): void
    {
        $collection = new Collection(['c' => 1,'a' => 6,'b' => 5]);
        $this->assertEquals(['a' => 6,'b' => 5,'c' => 1], $collection->sort()->toArray());
        $collection = new Collection(['c' => 1,'a' => 6,'b' => 5]);
        $this->assertEquals(['c' => 1,'b' => 5,'a' => 6], $collection->sort(null, SORT_DESC)->toArray());
    }

    public function testReverse(): void
    {
        $collection = new Collection([3,2,1]);
        $this->assertEquals([1,2,3], $collection->reverse()->toArray());
    }

    public function testSortBy(): void
    {
        $collection = new Collection(['a' => 10,'b' => 5,'c' => 20]);
        $this->assertEquals(
            ['b' => 5,'a' => 10,'c' => 20],
            $collection->sort(fn ($value) => $value)->toArray()
        );
    }

    public function testMin(): void
    {
        $this->assertNull((new Collection())->min(function ($value, $key) {
            return $value;
        }));

        $collection = new Collection(['a' => 10,'b' => 5,'c' => 20]);

        $this->assertEquals(5, $collection->min(function ($value, $key) {
            return $value;
        }));
    }

    public function testMax(): void
    {
        $this->assertNull((new Collection())->max(function ($value, $key) {
            return $value;
        }));
        $collection = new Collection(['a' => 10,'b' => 5,'c' => 20]);

        $this->assertEquals(5, $collection->min(function ($value, $key) {
            return $value;
        }));
    }

    public function testSlice(): void
    {
        $collection = new Collection([1,2,3,4,5,6,7,8,9,10]);
        $this->assertEquals([8,9,10], $collection->slice(7)->toArray());
        $this->assertEquals([4,5,6,7], $collection->slice(3, 4)->toArray());
    }

    public function testSerialize(): void
    {
        $collection = new Collection([1,2,3]);
        $expected = 'O:28:"Lightning\Utility\Collection":3:{i:0;i:1;i:1;i:2;i:2;i:3;}';
        $this->assertEquals($expected, serialize($collection));
        $this->assertEquals($collection, unserialize($expected));
    }
}
