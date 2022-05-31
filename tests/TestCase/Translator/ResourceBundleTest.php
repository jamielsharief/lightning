<?php declare(strict_types=1);

namespace Lightning\Test\Locale;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Lightning\Translator\ResourceBundle;
use Lightning\Translator\Exception\ResourceNotFoundException;

final class ResourceBundleTest extends TestCase
{
    public function testGet(): void
    {
        $bundle = new ResourceBundle([
            'hello_world' => 'Hello, World!',
        ]);
        $this->assertEquals('Hello, World!', $bundle->get('hello_world'));
    }

    public function testGetException(): void
    {
        $bundle = new ResourceBundle([
            'hello_world' => 'Hello, World!',
        ]);
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('No entry for `foo`');
        $bundle->get('foo');
    }

    public function testHas(): void
    {
        $bundle = new ResourceBundle([
            'hello_world' => 'Hello, World!',
        ]);
        $this->assertFalse($bundle->has('foo'));
        $this->assertTrue($bundle->has('hello_world'));
    }

    public function testCount(): void
    {
        $bundle = new ResourceBundle([
            'hello_world' => 'Hello, World!',
            'foo' => 'bar'
        ]);

        $this->assertCount(
            2, $bundle
        );
    }

    public function testGetIterator(): void
    {
        $bundle = new ResourceBundle([
            'hello_world' => 'Hello, World!',
            'foo' => 'bar'
        ]);

        $this->assertInstanceOf(ArrayIterator::class, $bundle->getIterator());
        $this->assertCount(2, $bundle->getIterator());
    }
}
