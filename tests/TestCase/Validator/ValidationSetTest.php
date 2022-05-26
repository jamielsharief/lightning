<?php declare(strict_types=1);

namespace Lightning\Test\Validator;

use PHPUnit\Framework\TestCase;
use Lightning\Validator\ValidationSet;

class ValidationSetTest extends TestCase
{
    public function testAlpha(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->alpha());

        $expected = [
            [
                'rule' => 'alpha',
                'args' => [],
                'message' => 'must only contain alphabetic characters',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testAlphaNumeric(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->alphaNumeric());

        $expected = [
            [
                'rule' => 'alphaNumeric',
                'args' => [],
                'message' => 'must only contain alpha numeric characters',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testNotNull(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->notNull());

        $expected = [
            [
                'rule' => 'notNull',
                'args' => [],
                'message' => 'must not be null',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testNotEmpty(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->notEmpty());

        $expected = [
            [
                'rule' => 'notEmpty',
                'args' => [],
                'message' => 'must not be empty',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testNotBlank(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->notBlank());

        $expected = [
            [
                'rule' => 'notBlank',
                'args' => [],
                'message' => 'must not be blank',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testEmail(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->email(true));

        $expected = [
            [
                'rule' => 'email',
                'args' => [
                    true
                ],
                'message' => 'invalid email address',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testIn(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->in(['foo'], true));

        $expected = [
            [
                'rule' => 'in',
                'args' => [
                    ['foo'],
                    true
                ],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testNotIn(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->notIn(['foo'], true));

        $expected = [
            [
                'rule' => 'notIn',
                'args' => [
                    ['foo'],
                    true
                ],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testLength(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->length(10));

        $expected = [
            [
                'rule' => 'length',
                'args' => [
                    10
                ],
                'message' => 'must be 10 characters',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testMinLength(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->minLength(10));

        $expected = [
            [
                'rule' => 'minLength',
                'args' => [
                    10
                ],
                'message' => 'must be a minimum of 10 characters',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testMaxLength(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->maxLength(10));

        $expected = [
            [
                'rule' => 'maxLength',
                'args' => [
                    10
                ],
                'message' => 'must be a maximum of 10 characters',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testLengthBetween(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->lengthBetween(5, 10));

        $expected = [
            [
                'rule' => 'lengthBetween',
                'args' => [
                    5,10
                ],
                'message' => 'must be between 5 and 10 characters',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testGreaterThanOrEqualTo(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->greaterThanOrEqualTo(10, 'must be greater than or equal to {min}'));

        $expected = [
            [
                'rule' => 'greaterThanOrEqualTo',
                'args' => [
                    10
                ],
                'message' => 'must be greater than or equal to 10',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testGreaterThan(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->greaterThan(10, 'must be greater than {min}'));

        $expected = [
            [
                'rule' => 'greaterThan',
                'args' => [
                    10
                ],
                'message' => 'must be greater than 10',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testLessThanOrEqualTo(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->lessThanOrEqualTo(10, 'must be less than or equal to {max}'));

        $expected = [
            [
                'rule' => 'lessThanOrEqualTo',
                'args' => [
                    10
                ],
                'message' => 'must be less than or equal to 10',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testLessThan(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->lessThan(10, 'must be less than {max}'));

        $expected = [
            [
                'rule' => 'lessThan',
                'args' => [
                    10
                ],
                'message' => 'must be less than 10',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testRange(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->range(5, 10, 'must be between {min} and {max}'));

        $expected = [
            [
                'rule' => 'range',
                'args' => [
                    5,10
                ],
                'message' => 'must be between 5 and 10',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testString(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->string());

        $expected = [
            [
                'rule' => 'string',
                'args' => [],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testNumeric(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->numeric());

        $expected = [
            [
                'rule' => 'numeric',
                'args' => [],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testDecimal(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->decimal());

        $expected = [
            [
                'rule' => 'decimal',
                'args' => [],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testArray(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->array());

        $expected = [
            [
                'rule' => 'array',
                'args' => [],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testBoolean(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->boolean());

        $expected = [
            [
                'rule' => 'boolean',
                'args' => [],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testDate(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->date());

        $expected = [
            [
                'rule' => 'dateTime',
                'args' => ['Y-m-d'],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testDatetime(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->datetime());

        $expected = [
            [
                'rule' => 'dateTime',
                'args' => ['Y-m-d H:i:s'],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testTime(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->time());

        $expected = [
            [
                'rule' => 'dateTime',
                'args' => ['H:i:s'],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testBefore(): Void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->before('today'));

        $expected = [
            [
                'rule' => 'before',
                'args' => ['today'],
                'message' => 'invalid date',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testAfter(): Void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->after('today'));

        $expected = [
            [
                'rule' => 'after',
                'args' => ['today'],
                'message' => 'invalid date',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testPattern(): Void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->regularExpression('/^[a-z]$/'));

        $expected = [
            [
                'rule' => 'regularExpression',
                'args' => ['/^[a-z]$/'],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testUrl(): Void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->url(false));

        $expected = [
            [
                'rule' => 'url',
                'args' => [false],
                'message' => 'invalid URL',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testCallable(): Void
    {
        $callable = function () {
            return true;
        };
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->callable($callable));

        $expected = [
            [
                'rule' => 'callable',
                'args' => [$callable],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testOptional(): Void
    {
        $validationSet = new ValidationSet();
        $this->assertFalse($validationSet->isOptional());
        $this->assertTrue($validationSet->optional()->isOptional());
    }

    public function testMethod(): Void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->method('foo', [1,2,3]));

        $expected = [
            [
                'rule' => 'foo',
                'args' => [1,2,3],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testEqualTo(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->equalTo(5));

        $expected = [
            [
                'rule' => 'equalTo',
                'args' => [5],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }

    public function testNotEqualTo(): void
    {
        $validationSet = new ValidationSet();
        $this->assertInstanceOf(ValidationSet::class, $validationSet->notEqualTo(5));

        $expected = [
            [
                'rule' => 'notEqualTo',
                'args' => [5],
                'message' => 'invalid value',
            ]
        ];

        $this->assertEquals($expected, $validationSet->toArray());
    }
}
