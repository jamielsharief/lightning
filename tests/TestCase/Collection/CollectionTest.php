<?php declare(strict_types=1);

namespace Lightning\Test\Collection;

use stdClass;
use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Lightning\Collection\Collection;

final class CollectionTest extends TestCase
{
    public function testToArray(): void
    {
        $collection = new Collection([1 => 'foo',2 => 'bar',3 => 'foobar']);
        $this->assertEquals(
            [1 => 'foo',2 => 'bar',3 => 'foobar'],
            $collection->toArray()
        );
    }

    public function testToList(): void
    {
        $collection = new Collection([1 => 'foo',2 => 'bar',3 => 'foobar']);
        $this->assertEquals(
            [0 => 'foo',1 => 'bar',2 => 'foobar'],
            $collection->toList()
        );
    }

    public function testMap(): void
    {
        $collection = new Collection(['foo','bar','foobar']);
        $newCollection = $collection->map(function ($item) {
            return strtoupper($item);
        });
        $this->assertEquals(['FOO','BAR','FOOBAR'], $newCollection->toArray());
    }

    public function testEach(): void
    {
        $data = ['foo','bar','foobar'];
        $collection = new Collection(array_merge($data, ['xxx']));

        $output = [];
        $collection->each(function ($item) use (&$output) {
            if ($item === 'xxx') {
                return false;
            }
            $output[] = $item;
        });
        $this->assertEquals($data, $output);
    }

    public function testExtract(): void
    {
        $data = [
            [
                'id' => 1000
            ],
            [
                'id' => 1001
            ],
            [
                'id' => 1002
            ],
        ];
        $collection = new Collection($data);

        $this->assertEquals(
            [1000,1001,1002],
            $collection->extract('id')->toArray()
        );
    }

    public function testExtractingDataFromPath(): void
    {
        $data = [
            [
                'id' => 1000,
                'user' => [
                    'id' => 2000
                ]
            ],
            [
                'id' => 1001,
                'user' => [
                    'id' => 2001
                ]
            ],
            [
                'id' => 1002,
                'user' => (object) [
                    'id' => 2002
                ]
            ],
        ];
        $collection = new Collection($data);

        $this->assertEquals(
            [2000,2001,2002],
            $collection->extract('user.id')->toArray()
        );

        $this->assertEquals([null,null,null], $collection->extract('foo')->toArray());
        $this->assertEquals([null,null,null], $collection->extract('foo.bar')->toArray());
    }

    public function testExtractingDataFromPathObject(): void
    {
        $object = new StdClass();
        $object->bar = 'ok';

        $data = [
            [
                'foo' => $object
            ]
        ];
        $collection = new Collection($data);

        $this->assertEquals(['ok'], $collection->extract('foo.bar')->toArray());
        $this->assertEquals([null], $collection->extract('foo.choo')->toArray());
    }

    public function testFilter(): void
    {
        $data = ['foo','bar','foobar'];

        $collection = (new Collection($data))->filter(function ($item) {
            return $item === 'foo';
        });
        $this->assertEquals(['foo'], $collection->toArray());

        $collection = (new Collection($data))->filter(function ($item) {
            return $item !== 'foo';
        });
        $this->assertEquals([1 => 'bar',2 => 'foobar'], $collection->toArray());
    }

    public function testIndexBy(): void
    {
        $data = [
            [
                'id' => 1000,
                'name' => 'Foo'
            ],
            [
                'id' => 1001,
                'name' => 'Bar'
            ],
            [
                'id' => 1002,
                'name' => 'FooBar'
            ],
        ];
        $collection = (new Collection($data))->indexBy('id');

        $result = $collection->toArray();
        $this->assertArrayHasKey(1000, $result);
        $this->assertEquals([
            'id' => 1000,
            'name' => 'Foo'
        ], $result[1000]);
    }

    public function testGroupBy(): void
    {
        $data = [
            [
                'id' => 1000,
                'name' => 'Foo',
                'category' => 'new'
            ],
            [
                'id' => 1001,
                'name' => 'Bar',
                'category' => 'draft'
            ],
            [
                'id' => 1002,
                'name' => 'FooBar',
                'category' => 'new'
            ],
        ];
        $collection = (new Collection($data))->groupBy('category');

        $result = $collection->toArray();

        $this->assertEquals([
            'new' => [
                0 => [
                    'id' => 1000,
                    'name' => 'Foo',
                    'category' => 'new'
                ],
                1 => [
                    'id' => 1002,
                    'name' => 'FooBar',
                    'category' => 'new'
                ]
            ],
            'draft' => [
                0 => [
                    'id' => 1001,
                    'name' => 'Bar',
                    'category' => 'draft'
                ]
            ]
        ], $result);
    }

    public function testChunk(): void
    {
        $data = ['a','b','c','d','e'];
        $collection = (new Collection($data))->chunk(2);

        $this->assertEquals(
            [
                ['a','b'],
                ['c','d'],
                ['e']
            ],
            $collection->toArray()
        );
    }

    public function testSort(): void
    {
        $data = [0 => 'a', 2 => 'c',1 => 'b'];
        $collection = (new Collection($data))->sort();
        $this->assertEquals(
            ['a','b','c'],
            $collection->toList()
        );
        $collection = (new Collection($data))->sort(SORT_DESC);
        $this->assertEquals(
            ['c','b','a'],
            $collection->toList()
        );
    }

    public function testSortBy(): void
    {
        $data = [
            [
                'name' => 'jon'
            ],
            [
                'name' => 'adam'
            ],
            [
                'name' => 'claire'
            ]
        ];
        $collection = (new Collection($data))->sortBy('name');

        $this->assertEquals(
            ['adam','claire','jon'],
            $collection->extract('name')->toList()
        );

        $collection = (new Collection($data))->sortBy('name', SORT_DESC);
        $this->assertEquals(
            array_reverse(['adam','claire','jon']),
            $collection->extract('name')->toList()
        );
    }

    public function testCount(): void
    {
        $collection = new Collection([]);
        $this->assertEquals(0, $collection->count());

        $collection = new Collection(['a','b','c','d','e']);
        $this->assertEquals(5, $collection->count());
    }

    public function testIsEmpty(): void
    {
        $collection = new Collection([]);
        $this->assertTrue($collection->isEmpty());

        $collection = new Collection(['a','b','c','d','e']);
        $this->assertFalse($collection->isEmpty());
    }

    public function testFirst()
    {
        $collection = new Collection([]);
        $this->assertNull($collection->first());

        $collection = new Collection(['a','b','c']);
        $this->assertEquals('a', $collection->first());
    }

    public function testLast(): void
    {
        $collection = new Collection([]);
        $this->assertNull($collection->last());

        $collection = new Collection(['a','b','c']);
        $this->assertEquals('c', $collection->last());
    }

    public function testMin(): void
    {
        $this->assertNull((new Collection([]))->min('id'));
        $data = [
            [
                'id' => 1005,
                'name' => 'Foo'
            ],
            [
                'id' => 1000,
                'name' => 'Bar'
            ],
            [
                'id' => 1006,
                'name' => 'FooBar'
            ],
        ];
        $collection = new Collection($data);
        $this->assertEquals([
            'id' => 1000,
            'name' => 'Bar'
        ], $collection->min('id'));
    }

    public function testMax(): void
    {
        $this->assertNull((new Collection([]))->max('id'));

        $data = [
            [
                'id' => 1005,
                'name' => 'Foo'
            ],
            [
                'id' => 1000,
                'name' => 'Bar'
            ],
            [
                'id' => 1006,
                'name' => 'FooBar'
            ],
        ];
        $collection = new Collection($data);
        $this->assertEquals([
            'id' => 1006,
            'name' => 'FooBar'
        ], $collection->max('id'));
    }

    public function testAvg(): void
    {
        $this->assertNull((new Collection([]))->avg('id'));

        $data = [
            [
                'id' => 1000,
                'name' => 'Foo'
            ],
            [
                'id' => 1010,
                'name' => 'Bar'
            ],
            [
                'id' => 1020,
                'name' => 'FooBar'
            ],
        ];
        $collection = new Collection($data);
        $this->assertEquals(1010, $collection->avg('id'));
    }

    public function testMedian(): void
    {
        $this->assertNull((new Collection([]))->median('id'));

        $data = [
            [
                'id' => 1000,
                'name' => 'Foo'
            ],
            [
                'id' => 1002,
                'name' => 'Bar'
            ],
            [
                'id' => 1005,
                'name' => 'FooBar'
            ],
        ];
        $collection = new Collection($data);
        $this->assertEquals(1002, $collection->median('id'));

        unset($data[2]);
        $collection = new Collection($data);
        $this->assertEquals(1001, $collection->median('id'));
    }

    public function testSumOf(): void
    {
        $this->assertEquals(0, (new Collection([]))->sumOf('id'));

        $data = [
            [
                'id' => 1000,

            ],
            [
                'id' => 1001,

            ],
            [
                'id' => 1002,
            ],
        ];
        $collection = new Collection($data);
        $this->assertEquals(3003, $collection->sumOf('id'));
    }

    public function testCountBy(): void
    {
        $this->assertEquals([], (new Collection([]))->countBy('id'));

        $data = [
            [
                'id' => 1000
            ],
            [
                'id' => 1001
            ],
            [
                'id' => 1002
            ],
        ];

        $collection = new Collection($data);
        $this->assertEquals(
            [
                'even' => 2,
                'odd' => 1
            ],
            $collection->countBy(function ($book) {
                return $book['id'] % 2 == 0 ? 'even' : 'odd';
            })
        );
    }

    public function testReduce(): void
    {
        $collection = new Collection([1,2,3]);
        $result = $collection->reduce(function ($accumulated, $value) {
            return $accumulated + $value;
        });
        $this->assertEquals(6, $result);
    }

    public function testFind()
    {
        $collection = new Collection(['a','b','c']);
        $this->assertEquals('b', $collection->find(function ($value) {
            return $value === 'b';
        }));

        $this->assertNull($collection->find(function ($value) {
            return $value === 'x';
        }));
    }

    public function testReject(): void
    {
        $collection = new Collection(['a','b','c']);
        $this->assertEquals([0 => 'a',2 => 'c'], $collection->reject(function ($value) {
            return $value === 'b';
        })->toArray());
    }

    public function testEvery(): void
    {
        $collection = new Collection(['a','b','c']);
        $this->assertTrue($collection->every(function ($value) {
            return is_string($value);
        }));

        $collection = new Collection(['a','b','c',1]);
        $this->assertFalse($collection->every(function ($value) {
            return is_string($value);
        }));
    }

    public function testSome(): void
    {
        $collection = new Collection(['a',1]);
        $this->assertTrue($collection->some(function ($value) {
            return is_string($value);
        }));

        $collection = new Collection([1,2]);
        $this->assertFalse($collection->some(function ($value) {
            return is_string($value);
        }));
    }

    public function testContains(): void
    {
        $collection = new Collection(['a','b','c']);
        $this->assertTrue($collection->contains('b'));
        $this->assertFalse($collection->contains('d'));
    }
    public function testArrayAccess(): void
    {
        $collection = new Collection(['a','b','c','d']);
        $this->assertTrue(isset($collection[0]));
        $this->assertEquals('b', $collection[1]);
        unset($collection[1]);
        $this->assertFalse(isset($collection[1]));
        $collection[1] = 'foo';
        $this->assertEquals('foo', $collection[1]);

        $collection[] = 'bar';

        $this->assertEquals('bar', $collection->last());
    }

    public function testJsonSeralize(): void
    {
        $collection = new Collection([1 => 'foo',2 => 'bar',3 => 'foobar']);
        $this->assertEquals(
            [1 => 'foo',2 => 'bar',3 => 'foobar'],
            $collection->jsonSerialize()
        );
    }

    public function testGetIterator(): void
    {
        $collection = new Collection([1 => 'foo',2 => 'bar',3 => 'foobar']);
        $this->assertInstanceOf(ArrayIterator::class, $collection->getIterator());

        $this->assertEquals(
            [1 => 'foo',2 => 'bar',3 => 'foobar'],
           iterator_to_array($collection->getIterator())
        );
    }

    public function testArrayPreserveKeys(): void
    {
        $collection = new Collection([1 => 'foo',2 => 'bar',3 => 'foobar'], false);
        $this->assertEquals(
            [0 => 'foo',1 => 'bar',2 => 'foobar'],
            $collection->toArray()
        );
    }

    public function testObjectDontPreserveKeys(): void
    {
        $collection = new Collection([1 => 'foo',2 => 'bar',3 => 'foobar']);

        $collection = new Collection($collection, false);
        $this->assertEquals(
            [0 => 'foo',1 => 'bar',2 => 'foobar'],
            $collection->toArray()
        );
    }

    public function testObjectPreserveKeys(): void
    {
        $collection = new Collection([1 => 'foo',2 => 'bar',3 => 'foobar']);

        $collection = new Collection($collection, true);
        $this->assertEquals(
            [1 => 'foo',2 => 'bar',3 => 'foobar'],
            $collection->toArray()
        );
    }
}
