<?php declare(strict_types=1);

namespace Lightning\Test\DataMapper;

use PHPUnit\Framework\TestCase;
use Lightning\DataMapper\ResultSet;

final class ResultSetTest extends TestCase
{
    public function testCountable(): void
    {
        $this->assertCount(0, new ResultSet([]));
        $this->assertCount(3, new ResultSet([1,2,3]));
    }

    public function testFirst(): void
    {
        $resultSet = new ResultSet([]);
        $this->assertNull($resultSet->first());

        $resultSet = new ResultSet([1,2,3]);
        $this->assertEquals(1, $resultSet->first());
    }

    public function testIsEmpty(): void
    {
        $resultSet = new ResultSet([]);
        $this->assertTrue($resultSet->isEmpty());

        $resultSet = new ResultSet([1,2,3]);
        $this->assertFalse($resultSet->isEmpty());
    }

    public function testToArray(): void
    {
        $resultSet = new ResultSet([]);
        $this->assertEquals([], $resultSet->toArray());

        $resultSet = new ResultSet([1,2,3]);
        $this->assertEquals([1,2,3], $resultSet->toArray());
    }

    public function testToString(): void
    {
        $resultSet = new ResultSet([1,2,3]);
        $this->assertEquals('[1,2,3]', $resultSet->toString());
    }

    public function testStringable(): void
    {
        $resultSet = new ResultSet([1,2,3]);
        $this->assertEquals('[1,2,3]', (string) $resultSet);
    }

    public function testJsonSerializable(): void
    {
        $resultSet = new ResultSet([1,2,3]);
        $this->assertEquals('[1,2,3]', json_encode($resultSet));
    }

    public function testIteratorAggregate(): void
    {
        $resultSet = new ResultSet([1,2,3]);
        $this->assertEquals([1,2,3], iterator_to_array($resultSet));
    }

    public function testArrayAccess(): void
    {
        $resultSet = new ResultSet([]);

        $resultSet['key'] = 'value';
        $this->assertEquals('value', $resultSet['key']);
        $this->assertTrue(isset($resultSet['key']));
        unset($resultSet['key']);
        $this->assertFalse(isset($resultSet['key']));

        $resultSet = new ResultSet([]);
        $resultSet[] = 'foo';
        $this->assertEquals('foo', $resultSet->first());
    }

    public function testMap(): void
    {
        $resultSet = new ResultSet([
            ['id' => 100,'name' => 'Jon','status' => 'new'],
            ['id' => 200,'name' => 'Claire','status' => 'archived'],
            ['id' => 300,'name' => 'Betty','status' => 'new'],
        ]);

        $resultSet = $resultSet->map(function ($row) {
            $row['id'] += 1;

            return $row;
        });
        $this->assertEquals([101,201,301], array_column($resultSet->toArray(), 'id'));
    }

    public function testFilter(): void
    {
        $resultSet = new ResultSet([
            ['id' => 100,'name' => 'Jon','status' => 'new'],
            ['id' => 200,'name' => 'Claire','status' => 'archived'],
            ['id' => 300,'name' => 'Betty','status' => 'new'],
        ]);

        $resultSet = $resultSet->filter(function ($row) {
            return $row['status'] !== 'archived';
        });
        $this->assertEquals([100,300], array_column($resultSet->toArray(), 'id'));
    }

    public function testIndexBy(): void
    {
        $resultSet = new ResultSet([
            ['id' => 100,'name' => 'Jon','status' => 'new'],
            ['id' => 200,'name' => 'Claire','status' => 'archived'],
            ['id' => 300,'name' => 'Betty','status' => 'new'],
        ]);

        $resultSet = $resultSet->indexBy(function ($row) {
            return $row['id'];
        });
        $expected = [
            100 => [
                'id' => 100,
                'name' => 'Jon',
                'status' => 'new'
            ],
            200 => [
                'id' => 200,
                'name' => 'Claire',
                'status' => 'archived'
            ],
            300 => [
                'id' => 300,
                'name' => 'Betty',
                'status' => 'new'
            ]
        ];

        $this->assertEquals($expected, $resultSet->toArray());
    }

    public function testGroupBy(): void
    {
        $resultSet = new ResultSet([
            ['id' => 100,'name' => 'Jon','status' => 'new'],
            ['id' => 200,'name' => 'Claire','status' => 'archived'],
            ['id' => 300,'name' => 'Betty','status' => 'new'],
        ]);

        $resultSet = $resultSet->groupBy(function ($row) {
            return $row['status'];
        });

        $expected = [
            'new' => [
                0 => [
                    'id' => 100,
                    'name' => 'Jon',
                    'status' => 'new'
                ],
                1 => [
                    'id' => 300,
                    'name' => 'Betty',
                    'status' => 'new'
                ]
            ],
            'archived' => [
                0 => [
                    'id' => 200,
                    'name' => 'Claire',
                    'status' => 'archived'
                ]
            ]
        ];

        $this->assertEquals($expected, $resultSet->toArray());
    }
}
