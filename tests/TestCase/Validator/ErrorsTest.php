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

    public function testGetErrorsCount(): void
    {
        $errors = new Errors();
        $this->assertEquals(0, $errors->getErrorsCount('foo'));

        $errors->setError('foo', 'bar');

        $this->assertEquals(1, $errors->getErrorsCount('foo'));
        $this->assertEquals(0, $errors->getErrorsCount('bar'));

        $errors->setError('foo', 'bar');
        $this->assertEquals(2, $errors->getErrorsCount('foo'));
    }

    public function testCount(): void
    {
        $errors = new Errors();
        $this->assertCount(0, $errors);

        $errors->setError('foo', 'bar');
        $this->assertCount(1, $errors);

        $errors->setError('foo', 'bar');
        $this->assertCount(2, $errors);
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
