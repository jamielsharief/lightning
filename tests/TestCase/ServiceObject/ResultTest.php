<?php declare(strict_types=1);

namespace Lightning\Test\ServiceObject;

use PHPUnit\Framework\TestCase;
use Lightning\ServiceObject\Result;

final class ResultTest extends TestCase
{
    public function testIsSuccess(): void
    {
        $this->assertTrue((new Result(true))->isSuccess());
        $this->assertFalse((new Result(false))->isSuccess());
    }

    public function testIsError(): void
    {
        $this->assertTrue((new Result(false))->isError());
        $this->assertFalse((new Result(true))->isError());
    }

    public function testGetSuccess(): void
    {
        $this->assertTrue((new Result(true))->getSuccess());
        $this->assertFalse((new Result(false))->getSuccess());
    }

    public function testWithSuccess(): void
    {
        $result = new Result(false);

        $this->assertFalse($result->getSuccess());
        $this->assertTrue($result->withSuccess(true)->getSuccess());
    }

    public function testSetSuccess(): void
    {
        $result = new Result(true);
        $result->setSuccess(false);
        $this->assertFalse($result->getSuccess());
        $result->setSuccess(true);
        $this->assertTrue($result->getSuccess());
    }

    public function testHasData(): void
    {
        $this->assertTrue((new Result(true, ['foo']))->hasData());
        $this->assertFalse((new Result(true))->hasData());
    }

    public function testGetData(): void
    {
        $result = new Result(true, ['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $result->getData());
        $this->assertEmpty((new Result(true))->getData());
    }

    public function testGet(): void
    {
        $result = new Result(true, ['foo' => 'bar']);
        $this->assertEquals('bar', $result->get('foo'));
        $this->assertNull($result->get('bar'));
    }

    public function testSetData(): void
    {
        $result = new Result(true);
        $result->setData(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $result->getData());
        $result->setData(['bar' => 'foo']);
        $this->assertEquals(['bar' => 'foo'], $result->getData());
    }

    public function testToString(): void
    {
        $this->assertEquals(
            '{"success":true,"data":{"foo":"bar"}}',
            (string) new Result(true, ['foo' => 'bar'])
        );
    }

    public function testJsonSerializeable(): void
    {
        $result = new Result(true, ['foo' => 'bar']);
        $this->assertEquals(
            ['success' => true,'data' => ['foo' => 'bar']],
            $result->jsonSerialize()
        );
    }

    public function testToArray(): void
    {
        $result = new Result(true, ['foo' => 'bar']);
        $this->assertEquals(
            ['success' => true,'data' => ['foo' => 'bar']],
            $result->toArray()
        );
    }
}
