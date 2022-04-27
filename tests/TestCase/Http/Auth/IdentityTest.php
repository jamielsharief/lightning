<?php declare(strict_types=1);

namespace Lightning\Test\Http\Auth;

use PHPUnit\Framework\TestCase;
use Lightning\Http\Auth\Identity;

final class IdentityTest extends TestCase
{
    public function testGetData(): void
    {
        $identity = new Identity(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $identity->getData());
    }

    public function testGet(): void
    {
        $identity = new Identity(['foo' => 'bar']);
        $this->assertEquals('bar', $identity->get('foo'));
        $this->assertNull($identity->get('bar'));
    }

    public function testSetData(): void
    {
        $identity = new Identity([]);
        $this->assertInstanceOf(Identity::class, $identity->setData(['foo' => 'bar']));
        $this->assertEquals(['foo' => 'bar'], $identity->getData());
    }

    public function testWithData(): void
    {
        $identity = new Identity([]);
        $identity = $identity->withData(['bar' => 'foo']);
        $this->assertInstanceOf(Identity::class, $identity);
        $this->assertEquals(['bar' => 'foo'], $identity->getData());
    }

    public function testToArray(): void
    {
        $identity = new Identity(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $identity->toArray());
    }

    public function testToString(): void
    {
        $identity = new Identity(['foo' => 'bar']);
        $this->assertEquals('{"foo":"bar"}', $identity->toString());
    }

    public function testToStringMagic(): void
    {
        $identity = new Identity(['foo' => 'bar']);
        $this->assertEquals('{"foo":"bar"}', (string) $identity);
    }

    public function testJsonSerialize(): void
    {
        $identity = new Identity(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $identity->jsonSerialize());
    }
}
