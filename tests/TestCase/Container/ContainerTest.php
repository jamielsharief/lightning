<?php declare(strict_types=1);

namespace Lightning\Test\Container;

use Lightning\Container\Container;
use Psr\Container\ContainerInterface;
use Lightning\Container\Exception\NotFoundException;

class Foo
{
    public bool $bar = false;
}

class Bar
{
    public function __construct(Foo $foo)
    {
    }
}

class FooBar
{
    public function foo(Foo $foo)
    {
        return $foo;
    }

    public function __invoke(Bar $bar)
    {
        return $bar;
    }
}

class StaticFoo
{
    public static function bar()
    {
    }
}

final class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testRegisterClass(): void
    {
        $container = new Container();
        $this->assertInstanceOf(ContainerInterface::class, $container->register(Foo::class));
    }

    public function testRegisterSingleton(): void
    {
        $container = new Container();

        $this->assertInstanceOf(ContainerInterface::class, $container->register(Foo::class, new Foo()));
    }

    public function testRegisterCallable(): void
    {
        $container = new Container();

        $this->assertInstanceOf(ContainerInterface::class, $container->register(Foo::class, function () {
            return new Foo();
        }));
    }

    public function testHas(): void
    {
        // Test without autoConfigure
        $container = new Container();
        $this->assertFalse($container->has(Foo::class));

        $container->register(Foo::class);
        $this->assertTrue($container->has(Foo::class));
    }

    public function testHasAutoConfigure(): void
    {
        // Test with autoConfigure enabled
        $container = new Container([]);
        $container->enableAutoConfigure();
        $this->assertTrue($container->has(Foo::class));
    }

    public function testGet(): void
    {
        $container = new Container([
            Foo::class => new Foo()
        ]);

        $this->assertInstanceOf(Foo::class, $container->get(Foo::class));
    }

    public function testGetNotFound(): void
    {
        $container = new Container();

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No defintion found for `Lightning\Test\Container\Foo`');
        $container->get(Foo::class);
    }

    public function testGetWithAutowiring(): void
    {
        $container = new Container([
            Bar::class,
        ]);
        $container->enableAutowiring();

        $this->assertInstanceOf(Bar::class, $container->get(Bar::class));
    }

    public function testGetFromClass(): void
    {
        $container = new Container();
        $container->register(Foo::class);
        $this->assertInstanceOf(Foo::class, $container->get(Foo::class));
    }

    public function testGetCallable(): void
    {
        $container = new Container();
        $container->register(Foo::class, function () {
            return new Foo();
        });

        $this->assertInstanceOf(Foo::class, $container->get(Foo::class));
    }

    public function testGetSingleton(): void
    {
        $container = new Container();
        $container->register(Foo::class, new Foo());

        $this->assertInstanceOf(Foo::class, $container->get(Foo::class));
    }

    public function testGetIsShared(): void
    {
        $container = new Container();
        $container->register(Foo::class);

        $foo = $container->get(Foo::class);
        $foo->bar = true;

        $foo = $container->get(Foo::class);
        $this->assertTrue($foo->bar);
    }

    /**
     * @depends testGetIsShared
     */
    public function testRegisterIsNewInstance(): void
    {
        $container = new Container();
        $container->register(Foo::class);

        $foo = $container->get(Foo::class);
        $foo->bar = true;

        $foo = $container->resolve(Foo::class);
        $this->assertFalse($foo->bar);
    }

    public function testResolveError(): void
    {
        $container = new Container();

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No defintion found for `Lightning\Test\Container\Foo`');
        $container->get(Foo::class);
    }
}
