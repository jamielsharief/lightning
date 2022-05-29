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
        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'), 'messages');
        $this->assertEquals('Hello, World!', $bundle->get('hello_world'));
    }

    public function testGetException(): void
    {
        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'), 'messages');
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('No entry for `foo`');
        $bundle->get('foo');
    }

    public function testHas(): void
    {
        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_GB'), 'messages');
        $this->assertFalse($bundle->has('foo'));
        $this->assertTrue($bundle->has('hello_mate'));
    }

    public function testSetLocale(): void
    {
        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'), 'messages');
        $this->assertTrue($bundle->setLocale(new Locale('en_GB'))->has('hello_mate'));
    }

    public function testGetLocale(): void
    {
        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'), 'messages');
        $locale = new Locale('en_GB');

        $this->assertEquals($locale, $bundle->setLocale($locale)->getLocale());
    }

    public function testWithLocale(): void
    {
        $english = new Locale('en_GB');
        $american = new Locale('en_US');
        $bundle = new ResourceBundle(__DIR__ . '/resources', $american, 'messages');
        $this->assertEquals($english, $bundle->withLocale($english)->getLocale());
        $this->assertEquals($american, $bundle->getLocale());
    }

    public function testSetLocaleException(): void
    {
        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'), 'messages');
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Resource bundle `messages.zh-CN.php');

        $bundle->setLocale(new Locale('zh-CN'));
    }

    public function testGetName(): void
    {
        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'));
        $this->assertEquals('resources', $bundle->getName());

        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'), 'messages');
        $this->assertEquals('messages', $bundle->getName());
    }

    public function testSetName(): void
    {
        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'));
        $this->assertEquals('messages', $bundle->setName('messages')->getName());
    }

    public function testSetNameException(): void
    {
        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'), 'messages');
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Resource bundle `foo.en_US.php');
        $bundle->setName('foo');
    }

    public function testWithName(): void
    {
        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'));

        $this->assertEquals('messages', $bundle->withName('messages')->getName());
        $this->assertEquals('resources', $bundle->getName());
    }

    public function testCount(): void
    {
        $this->assertCount(
            4,
            new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'), 'messages')
        );
    }

    public function testGetIterator(): void
    {
        $bundle = new ResourceBundle(__DIR__ . '/resources', new Locale('en_US'), 'messages');

        $this->assertInstanceOf(ArrayIterator::class, $bundle->getIterator());
        $this->assertCount(4, $bundle->getIterator());
    }
}
