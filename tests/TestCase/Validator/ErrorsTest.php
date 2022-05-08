<?php declare(strict_types=1);

namespace Lightning\Test\Validator;

use Lightning\Validator\Errors;
use PHPUnit\Framework\TestCase;

class ErrorsTest extends TestCase
{
    public function testSetErrors(): void
    {
        $errors = new Errors();

        $this->assertInstanceOf(
            Errors::class,
            $errors->setErrors(['email' => ['foo' => 'bar']])
        );
    }

    public function testSetError(): void
    {
        $errors = new Errors();

        $this->assertInstanceOf(
            Errors::class,
            $errors->setError('foo', 'bar')
        );
    }

    public function testGetErrors(): void
    {
        $errors = new Errors();
        $this->assertEmpty($errors->getErrors());
        $this->assertEmpty($errors->getErrors('foo'));

        $errors->setError('foo', 'bar');

        $this->assertEquals(['bar'], $errors->getErrors('foo'));
        $this->assertEquals(['foo' => ['bar']], $errors->getErrors());
    }

    public function testHasErrors(): void
    {
        $errors = new Errors();

        $this->assertFalse($errors->hasErrors());
        $this->assertFalse($errors->hasErrors('foo'));

        $errors->setError('foo', 'bar');

        $this->assertTrue($errors->hasErrors());
        $this->assertTrue($errors->hasErrors('foo'));
    }

    public function testReset(): void
    {
        $errors = new Errors();
        $this->assertFalse($errors->hasErrors());
        $errors->setError('foo', 'bar');
        $this->assertTrue($errors->hasErrors());
        $this->assertInstanceOf(
            Errors::class,
            $errors->reset()
        );
        $this->assertFalse($errors->hasErrors());
    }
}
