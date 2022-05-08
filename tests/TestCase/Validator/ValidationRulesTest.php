<?php declare(strict_types=1);

namespace Lightning\Test\Validator;

use DateTime;
use Countable;
use PHPUnit\Framework\TestCase;
use Lightning\Validator\ValidationRules;

class Storage implements Countable
{
    public function __construct(private int $count)
    {
    }
    public function count(): int
    {
        return $this->count;
    }
}

final class ValidationRulesTest extends TestCase
{
    public function testAssertTrue(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->boolean(null));

        $this->assertTrue($validation->boolean(true));
        $this->assertTrue($validation->boolean(false));

        $this->assertTrue($validation->boolean('true'));
        $this->assertTrue($validation->boolean('false'));

        $this->assertTrue($validation->boolean(0));
        $this->assertTrue($validation->boolean(1));

        $this->assertTrue($validation->boolean('0'));
        $this->assertTrue($validation->boolean('1'));
    }

    public function testNull(): void
    {
        $validation = new ValidationRules();
        $this->assertTrue($validation->null(null));
        $this->assertFalse($validation->null(''));
    }

    public function testNotNull(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->notNull(null));
        $this->assertTrue($validation->notNull(''));
    }

    public function testEmpty(): void
    {
        $validation = new ValidationRules();

        $this->assertTrue($validation->empty(null));
        $this->assertTrue($validation->empty('  '));
        $this->assertTrue($validation->empty(''));
        $this->assertFalse($validation->empty(false));

        $this->assertTrue($validation->empty([]));
        $this->assertFalse($validation->empty(['foo']));

        $this->assertTrue($validation->empty(new Storage(0)));
        $this->assertFalse($validation->empty(new Storage(5)));
    }

    public function testNotEmpty(): void
    {
        $validation = new ValidationRules();
        $this->assertTrue($validation->notEmpty('foo'));
        $this->assertTrue($validation->notEmpty(['foo']));
        $this->assertTrue($validation->notEmpty(new Storage(5)));

        $this->assertFalse($validation->notEmpty(null));
        $this->assertFalse($validation->notEmpty(''));
        $this->assertFalse($validation->notEmpty([]));
        $this->assertFalse($validation->notEmpty(new Storage(0)));
    }

    public function testNotBlank(): void
    {
        $validation = new ValidationRules();
        $this->assertTrue($validation->notBlank('foo'));
        $this->assertFalse($validation->notBlank(null));
        $this->assertFalse($validation->notBlank(''));
    }

    public function testEmail(): void
    {
        $validation = new ValidationRules();
        $this->assertTrue($validation->email('user@example.com'));
        $this->assertTrue($validation->email('user@virgin.com', true));

        $this->assertFalse($validation->email('user@some-domain-that-should-not-exist.com', true));

        $this->assertFalse($validation->email('This is not an email')); // r
        $this->assertFalse($validation->email(''));
        $this->assertFalse($validation->email(null));
    }

    public function testLength(): void
    {
        $validation = new ValidationRules();
        $this->assertTrue($validation->length('foo', 3));

        $this->assertFalse($validation->length('bar', 4));
        $this->assertFalse($validation->length(null, 0));
    }

    public function testMaxLength(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->maxLength(null, 0));

        $this->assertTrue($validation->maxLength('foo', 3));
        $this->assertTrue($validation->maxLength('foo', 4));
        $this->assertFalse($validation->maxLength('foo', 2));
    }

    public function testMinLength(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->minLength(null, 0));

        $this->assertTrue($validation->minLength('foo', 2));
        $this->assertTrue($validation->minLength('foo', 3));
        $this->assertFalse($validation->minLength('foo', 4));
    }

    public function testMax(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->lessThanOrEqualTo(null, 5));

        $this->assertTrue($validation->lessThanOrEqualTo(4.5, 5));
        $this->assertTrue($validation->lessThanOrEqualTo(5, 5));
        $this->assertFalse($validation->lessThanOrEqualTo(5, 4));
    }

    public function testMin(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->greaterThanOrEqualTo(null, 5));

        $this->assertTrue($validation->greaterThanOrEqualTo(4.5, 1));
        $this->assertTrue($validation->greaterThanOrEqualTo(1, 1));
        $this->assertFalse($validation->greaterThanOrEqualTo(1, 2));
    }

    public function testInteger(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->integer(null));

        $this->assertTrue($validation->integer(5));
        $this->assertTrue($validation->integer('5'));
        $this->assertFalse($validation->integer(0.5));
        $this->assertFalse($validation->integer('5.5'));
    }

    public function testDecimal(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->decimal(null));

        $this->assertTrue($validation->decimal(5.0));
        $this->assertTrue($validation->decimal('5.5'));

        $this->assertFalse($validation->decimal(5));
        $this->assertFalse($validation->decimal('5'));
    }

    public function testNumeric(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->numeric(null));

        $this->assertTrue($validation->numeric(5));
        $this->assertTrue($validation->numeric(5.5));
        $this->assertTrue($validation->numeric('5'));
        $this->assertTrue($validation->numeric('5.5'));
        $this->assertFalse($validation->numeric('five'));
    }

    public function testString(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->string(null));

        $this->assertTrue($validation->string(''));
        $this->assertFalse($validation->string(false));
    }

    public function testCount(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->count(null, 0));

        $this->assertTrue($validation->count([], 0));
        $this->assertTrue($validation->count(['1'], 1));
        $this->assertFalse($validation->count(['1'], 2));

        $this->assertTrue($validation->count(new Storage(0), 0));
        $this->assertTrue($validation->count(new Storage(5), 5));
        $this->assertFalse($validation->count(new Storage(5), 3));
    }

    public function testDateTime(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->dateTime(null));

        $this->assertTrue($validation->dateTime('2022-01-01 10:00:00'));
        $this->assertFalse($validation->dateTime('2022-01-01'));
        $this->assertTrue($validation->dateTime('2022-01-01', 'Y-m-d'));
    }

    public function testBefore(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->before(null));
        $this->assertTrue($validation->before('2022-01-01', '2022-01-02'));
        $this->assertTrue($validation->before('today', 'tomorrow'));
        $this->assertTrue($validation->before(new DateTime('today'), 'tomorrow'));
        $this->assertFalse($validation->before(new DateTime('yesterday'), 'yesterday'));
    }

    public function testAfter(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->after(null));
        $this->assertTrue($validation->after('2022-01-02', '2022-01-01'));
        $this->assertTrue($validation->after('tomorrow', 'yesterday'));
        $this->assertTrue($validation->after(new DateTime('today'), 'yesterday'));
        $this->assertFalse($validation->after(new DateTime('yesterday'), 'yesterday'));
    }

    public function testUrl(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->url(null));
        $this->assertTrue($validation->url('https://www.google.co.uk'));
        $this->assertTrue($validation->url('https://www.google.co.uk', true));
        $this->assertTrue($validation->url('www.google.co.uk', false));

        $this->assertFalse($validation->url('hello'));
        $this->assertFalse($validation->url('www.google.co.uk'));
    }

    public function testIn(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->in(null, []));

        $this->assertTrue($validation->in(1, [1,2,3]));
        $this->assertFalse($validation->in(5, [1,2,3]));

        $this->assertTrue($validation->in('C', ['a','b','c'], true));
        $this->assertTrue($validation->in('b', ['a','B','c'], true));
    }

    public function testNotIn(): void
    {
        $validation = new ValidationRules();

        $this->assertFalse($validation->notIn('a', ['a','b','c']));
        $this->assertTrue($validation->notIn('B', ['a','b','c']));
        $this->assertFalse($validation->notIn('B', ['a','b','c'], true));
    }

    public function testPattern(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->regularExpression(null, '/^[a-z]+$/'));

        $this->assertTrue($validation->regularExpression('abcd', '/^[a-z]+$/'));
        $this->assertFalse($validation->regularExpression('ab-cd', '/^[a-z]+$/'));
    }

    public function testCallable(): void
    {
        $validation = new ValidationRules();
        $this->assertTrue($validation->callable(1, function ($value) {
            return $value === 1;
        }));

        $this->assertFalse($validation->callable(2, function ($value) {
            return $value === 1;
        }));
    }

    public function testLengthBetween(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->lengthBetween(null, 0, 10));

        $this->assertTrue($validation->lengthBetween('foo', 1, 3));
        $this->assertTrue($validation->lengthBetween('foo', 3, 5));
        $this->assertTrue($validation->lengthBetween('foo', 2, 4));

        $this->assertFalse($validation->lengthBetween('foo', 4, 5));
        $this->assertFalse($validation->lengthBetween('foo', 1, 2));
    }

    public function testAlpha(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->alpha(null));

        $this->assertTrue($validation->alpha('abcd'));
        $this->assertFalse($validation->alpha('1234'));
    }

    public function testAlphanumeric(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->alphaNumeric(null));

        $this->assertTrue($validation->alphaNumeric('abcd1234'));
        $this->assertFalse($validation->alphaNumeric('abcd1234@'));
    }

    public function testArray(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->array(null));

        $this->assertTrue($validation->array([]));
    }

    public function testRange(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->range(null, 0, 10));

        $this->assertTrue($validation->range(1, 1, 3));
        $this->assertTrue($validation->range(2, 1, 3));
        $this->assertTrue($validation->range(3, 1, 3));

        $this->assertTrue($validation->range(2.5, 1.5, 3.5));

        $this->assertFalse($validation->range(4, 1, 3));
    }

    public function testEqualTo(): void
    {
        $validation = new ValidationRules();

        $this->assertTrue($validation->equalTo(1, 1));
        $this->assertFalse($validation->equalTo(1, '1'));
    }

    public function testNotEqualTo(): void
    {
        $validation = new ValidationRules();

        $this->assertFalse($validation->notEqualTo(1, 1));
        $this->assertTrue($validation->notEqualTo(1, '1'));
    }

    public function testGreaterThan(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->greaterThan(null, 0));

        $this->assertTrue($validation->greaterThan(1, 0));
        $this->assertFalse($validation->greaterThan(1, 1));
    }

    public function testGreaterThanOrEqualTo(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->greaterThanOrEqualTo(null, 0));

        $this->assertTrue($validation->greaterThanOrEqualTo(1, 0));
        $this->assertTrue($validation->greaterThanOrEqualTo(1, 1));
        $this->assertFalse($validation->greaterThanOrEqualTo(1, 2));
    }

    public function testLessThan(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->lessThan(null, 0));

        $this->assertTrue($validation->lessThan(0, 1));
        $this->assertFalse($validation->lessThan(1, 0));
    }

    public function testLessThanOrEqualsTo(): void
    {
        $validation = new ValidationRules();
        $this->assertFalse($validation->lessThanOrEqualTo(null, 0));

        $this->assertTrue($validation->lessThanOrEqualTo(0, 1));
        $this->assertFalse($validation->lessThanOrEqualTo(1, 0));
    }
}
