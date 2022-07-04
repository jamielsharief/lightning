<?php declare(strict_types=1);

namespace Lightning\Test\Params;

use Lightning\Params\Params;
use PHPUnit\Framework\TestCase;
use Lightning\Params\Exception\UnknownParameterException;

final class ParamsTest extends TestCase
{
    public function testHas(): void
    {
        $params = new Params(['foo' => 'bar']);
        $this->assertTrue($params->has('foo'));
        $this->assertFalse($params->has('bar'));
    }

    public function testGet(): void
    {
        $this->assertEquals(
            'bar',
            (new Params(['foo' => 'bar']))->get('foo')
        );
    }

    public function testGetException(): void
    {
        $this->expectException(UnknownParameterException::class);
        $this->expectExceptionMessage('Unkown parameter `foo`');
        (new Params())->get('foo');
    }

    public function testSet(): void
    {
        $params = new Params();
        $this->assertInstanceOf(Params::class, $params->set('foo', 'bar'));
        $this->assertEquals('bar', $params->get('foo'));
    }

    public function testUnset(): void
    {
        $params = new Params(['foo' => 'bar']);
        $this->assertEquals('bar', $params->get('foo'));
        $this->assertInstanceOf(Params::class, $params->unset('foo', 'bar'));

        $this->expectException(UnknownParameterException::class);
        $this->expectExceptionMessage('Unkown parameter `foo`');

        $params->get('foo');
    }

    public function testGetArray(): void
    {
        $this->assertEquals(
            ['foo' => 'bar'],
            (new Params(['foo' => 'bar']))->toArray()
        );
    }
}
