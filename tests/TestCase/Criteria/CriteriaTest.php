<?php declare(strict_types=1);

namespace Lightning\Test\Criteria;

use stdClass;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\Criteria\Criteria;

final class CriteriaTest extends TestCase
{
    public function testNoKeyError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No key provided');

        $criteria = new Criteria(['foo']);
    }

    public function testInvalidExpression(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid expression `<-o->`');

        new Criteria(['foo <-o->' => 'bar']);
    }

    public function testInvalidValueForArithmetic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid comparison value for `foo`, did not expect array');

        new Criteria(['foo >' => ['xx']]);
    }

    public function testInvalidValueObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid comparison value for `foo`, object provided');

        new Criteria(['foo !=' => new stdClass()]);
    }

    public function testEquals()
    {
        $criteria = new Criteria(['id' => 1234]);

        $this->assertTrue($criteria->match(['id' => 1234]));
        $this->assertFalse($criteria->match(['id' => 5678]));
    }

    public function testEqualsArray()
    {
        $criteria = new Criteria(['id' => [1234]]);

        $this->assertTrue($criteria->match(['id' => 1234]));
        $this->assertFalse($criteria->match(['id' => 5678]));
    }

    public function testIn()
    {
        $criteria = new Criteria(['id IN' => [1234]]);

        $this->assertTrue($criteria->match(['id' => 1234]));
        $this->assertFalse($criteria->match(['id' => 5678]));
    }

    public function testInNonArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid comparison value for `foo`, expected an array');

        new Criteria(['foo IN' => 'bar']);
    }

    public function testNotIn()
    {
        $criteria = new Criteria(['id NOT IN' => [1234]]);

        $this->assertFalse($criteria->match(['id' => 1234]));
        $this->assertTrue($criteria->match(['id' => 5678]));
    }

    public function testNotInNonArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid comparison value for `foo`, expected an array');

        new Criteria(['foo NOT IN' => 'bar']);
    }

    public function testIsNull()
    {
        $criteria = new Criteria(['id' => null]);

        $this->assertTrue($criteria->match(['id' => null]));
        $this->assertFalse($criteria->match(['id' => 5678]));
    }

    public function testIsNotNull()
    {
        $criteria = new Criteria(['id !=' => null]);

        $this->assertFalse($criteria->match(['id' => null]));
        $this->assertTrue($criteria->match(['id' => 5678]));
    }

    public function testNotEquals()
    {
        $criteria = new Criteria(['id !=' => 1234]);

        $this->assertFalse($criteria->match(['id' => 1234]));
        $this->assertTrue($criteria->match(['id' => 5678]));

        $criteria = new Criteria(['id <>' => 1234]);

        $this->assertFalse($criteria->match(['id' => 1234]));
        $this->assertTrue($criteria->match(['id' => 5678]));
    }

    public function testNotEqualsArray()
    {
        $criteria = new Criteria(['id !=' => [1234]]);

        $this->assertFalse($criteria->match(['id' => 1234]));
        $this->assertTrue($criteria->match(['id' => 5678]));

        $criteria = new Criteria(['id <>' => [1234]]);

        $this->assertFalse($criteria->match(['id' => 1234]));
        $this->assertTrue($criteria->match(['id' => 5678]));
    }

    public function testGreaterThan()
    {
        $criteria = new Criteria(['id >' => 1000]);

        $this->assertTrue($criteria->match(['id' => 2000]));
        $this->assertFalse($criteria->match(['id' => 1000]));
    }

    public function testGreaterThanOrEqualTo()
    {
        $criteria = new Criteria(['id >=' => 1000]);

        $this->assertTrue($criteria->match(['id' => 1000]));
        $this->assertTrue($criteria->match(['id' => 1001]));
        $this->assertFalse($criteria->match(['id' => 999]));
    }

    public function testLessThan()
    {
        $criteria = new Criteria(['id <' => 2000]);

        $this->assertTrue($criteria->match(['id' => 1000]));
        $this->assertFalse($criteria->match(['id' => 2000]));
    }

    public function testLessThanOrEqualTo()
    {
        $criteria = new Criteria(['id <=' => 2000]);

        $this->assertTrue($criteria->match(['id' => 2000]));
        $this->assertTrue($criteria->match(['id' => 1999]));
        $this->assertFalse($criteria->match(['id' => 2001]));
    }

    public function testBetween()
    {
        $criteria = new Criteria(['id BETWEEN' => [1000,2000]]);

        $this->assertTrue($criteria->match(['id' => 1234]));
        $this->assertFalse($criteria->match(['id' => 5678]));
        $this->assertFalse($criteria->match(['id' => null]));
    }

    public function testBetweenNonArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid comparison value for `id`, expected an array with two values');
        new Criteria(['id BETWEEN' => 'foo']);
    }

    public function testNotBetween()
    {
        $criteria = new Criteria(['id NOT BETWEEN' => [1000,2000]]);

        $this->assertFalse($criteria->match(['id' => 1234]));
        $this->assertTrue($criteria->match(['id' => 5678]));
        $this->assertTrue($criteria->match(['id' => null]));
    }

    public function testNotBetweenNonArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid comparison value for `id`, expected an array with two values');
        new Criteria(['id NOT BETWEEN' => 'foo']);
    }

    public function testLikeStartsWith()
    {
        $criteria = new Criteria(['name LIKE' => 'j%']);
        $this->assertTrue($criteria->match(['name' => 'jon snow']));
        $this->assertFalse($criteria->match(['name' => 'tom snow']));
        $this->assertFalse($criteria->match(['name' => null]));
    }

    public function testLikeNonScalar()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid comparison value for `name`, expected a scalar or null value');
        new Criteria(['name LIKE' => ['xxx']]);
    }

    public function testNotLikeStartsWith()
    {
        $criteria = new Criteria(['name NOT LIKE' => 'j%']);
        $this->assertFalse($criteria->match(['name' => 'jon snow']));
        $this->assertTrue($criteria->match(['name' => 'tom snow']));
        $this->assertTrue($criteria->match(['name' => null]));
    }

    public function testLikeContains()
    {
        $criteria = new Criteria(['name LIKE' => 'j% snow']);
        $this->assertTrue($criteria->match(['name' => 'jon snow']));
        $this->assertFalse($criteria->match(['name' => 'tim snow']));
        $this->assertFalse($criteria->match(['name' => null]));
    }

    public function testNotLikeContains()
    {
        $criteria = new Criteria(['name NOT LIKE' => 'j% snow']);
        $this->assertFalse($criteria->match(['name' => 'jon snow']));
        $this->assertTrue($criteria->match(['name' => 'tim snow']));
        $this->assertTrue($criteria->match(['name' => null]));
    }

    public function testLikeEndsWith()
    {
        $criteria = new Criteria(['name LIKE' => '%n']);
        $this->assertTrue($criteria->match(['name' => 'jon']));
        $this->assertFalse($criteria->match(['name' => 'one']));
        $this->assertFalse($criteria->match(['name' => null]));
    }

    public function testNotLikeEndsWith()
    {
        $criteria = new Criteria(['name NOT LIKE' => '%n']);
        $this->assertFalse($criteria->match(['name' => 'jon']));
        $this->assertTrue($criteria->match(['name' => 'one']));
        $this->assertTrue($criteria->match(['name' => null]));
    }

    public function testLikeSingleChar()
    {
        $criteria = new Criteria(['name LIKE' => 'j_n']);
        $this->assertTrue($criteria->match(['name' => 'jon']));
        $this->assertFalse($criteria->match(['name' => 'joon']));
        $this->assertFalse($criteria->match(['name' => null]));
    }

    public function testNotLikeSingleChar()
    {
        $criteria = new Criteria(['name NOT LIKE' => 'j_n']);
        $this->assertFalse($criteria->match(['name' => 'jon']));
        $this->assertTrue($criteria->match(['name' => 'joon']));
        $this->assertTrue($criteria->match(['name' => null]));
    }

    public function testMultipleCriteria()
    {
        $criteria = new Criteria(['id' => 1234,'status' => 'active']);

        $this->assertFalse($criteria->match(['id' => 1000,'status' => 'unkown']));
        $this->assertFalse($criteria->match(['id' => 1234,'status' => 'unkown']));
        $this->assertFalse($criteria->match(['id' => 1000,'status' => 'active']));

        $this->assertTrue($criteria->match(['id' => 1234,'status' => 'active']));
    }

    public function testEmptyData()
    {
        $criteria = new Criteria(['id' => 1234,'status' => 'active']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data is missing key `id`');

        $criteria->match([]);
    }
}
