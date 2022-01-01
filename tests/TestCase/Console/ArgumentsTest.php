<?php declare(strict_types=1);

namespace Lightning\Test\Console;

use PHPUnit\Framework\TestCase;
use Lightning\Console\Arguments;

final class ArgumentsTest extends TestCase
{
    public function testSetArguments(): void
    {
        $args = new Arguments();
        $this->assertInstanceOf(Arguments::class, $args->setArguments(['foo' => 'bar']));
    }

    public function testGetArguments(): void
    {
        $args = new Arguments();
        $this->assertEmpty($args->getArguments());

        $args->setArguments(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $args->getArguments());
    }

    public function testGetArgument(): void
    {
        $args = new Arguments([], ['foo' => 'bar']);

        $this->assertNull($args->getArgument('none'));
        $this->assertEquals('bar', $args->getArgument('foo'));
    }

    public function testHasArgument(): void
    {
        $args = new Arguments([], ['foo' => 'bar']);
        $this->assertFalse($args->hasArgument('help'));
        $this->assertTrue($args->hasArgument('foo'));
    }

    public function testSetOptions(): void
    {
        $args = new Arguments();
        $this->assertInstanceOf(Arguments::class, $args->setOptions(['foo' => 'bar']));
    }

    public function testGetOptions(): void
    {
        $args = new Arguments();
        $this->assertEmpty($args->getOptions());

        $args->setOptions(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $args->getOptions());
    }

    public function testGetOption(): void
    {
        $args = new Arguments(['foo' => 'bar']);

        $this->assertNull($args->getOption('none'));
        $this->assertEquals('bar', $args->getOption('foo'));
    }

    public function testHasOption(): void
    {
        $args = new Arguments(['foo' => 'bar']);
        $this->assertFalse($args->hasOption('help'));
        $this->assertTrue($args->hasOption('foo'));
    }
}
