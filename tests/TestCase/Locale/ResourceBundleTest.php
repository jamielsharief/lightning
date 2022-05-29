<?php declare(strict_types=1);

namespace Lightning\Test\Locale;

use ArrayIterator;
use Lightning\Locale\Locale;
use PHPUnit\Framework\TestCase;
use Lightning\Locale\ResourceBundle;
use Lightning\Locale\Exception\ResourceNotFoundException;

final class ResourceBundleTest extends TestCase
{
    public function testGet(): void
    {
        $bundle = ResourceBundle::create(new Locale('en_US'), __DIR__ . '/resources/app');
        $this->assertEquals('Hello, World!', $bundle->get('hello_world'));
    }

    public function testGetException(): void
    {
        $bundle = ResourceBundle::create(new Locale('en_US'), __DIR__ . '/resources/app');
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('No entry for `foo`');
        $bundle->get('foo');
    }

    public function testHas(): void
    {
        $bundle = ResourceBundle::create(new Locale('en_GB'), __DIR__ . '/resources/app');
        $this->assertFalse($bundle->has('foo'));
        $this->assertTrue($bundle->has('hello_mate'));
    }

    public function testGetLocale(): void
    {
        $locale = new Locale('en_US');
        $bundle = ResourceBundle::create($locale, __DIR__ . '/resources/app');
        $this->assertEquals($locale, $bundle->getLocale());
    }

    public function testCount(): void
    {
        $this->assertCount(
            4,
            ResourceBundle::create(new Locale('en_GB'), __DIR__ . '/resources/app')
        );
    }

    public function testGetIterator(): void
    {
        $bundle = ResourceBundle::create(new Locale('en_GB'), __DIR__ . '/resources/app');

        $this->assertInstanceOf(ArrayIterator::class, $bundle->getIterator());
        $this->assertCount(4, $bundle->getIterator());
    }
}
