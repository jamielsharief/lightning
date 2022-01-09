<?php declare(strict_types=1);

namespace Lightning\Test\Query;

use InvalidArgumentException;
use Lightning\Hook\HookTrait;
use PHPUnit\Framework\TestCase;
use Lightning\Hook\HookInterface;

class ArticlesController implements HookInterface
{
    use HookTrait;

    public function beforeFilter()
    {
    }

    public function doSomething(bool $result)
    {
        return $result;
    }

    public function afterFilter()
    {
        return true;
    }

    public function getRegisteredHooks(string $name)
    {
        return $this->registeredHooks[$name] ?? [];
    }
}

final class HookTraitTest extends TestCase
{
    public function testRegister(): void
    {
        $controller = new ArticlesController();

        $controller->registerHook('before', 'beforeFilter');
        $this->assertEquals(
            ['beforeFilter'],
            $controller->getRegisteredHooks('before')
        );
    }

    public function testRegisterException(): void
    {
        $controller = new ArticlesController();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hook method `foo` does not exist');
        $controller->registerHook('before', 'foo');
    }

    public function testRegisterMany(): void
    {
        $controller = new ArticlesController();
        $controller->registerHook('before', 'beforeFilter');
        $controller->registerHook('before', 'doSomething');
        $this->assertEquals(
            ['beforeFilter','doSomething'],
            $controller->getRegisteredHooks('before')
        );
    }

    public function testTrigger(): void
    {
        $controller = new ArticlesController();
        $controller->registerHook('before', 'beforeFilter');
        $this->assertTrue($controller->triggerHook('before', [true]));
    }

    public function testTriggerButStopped(): void
    {
        $controller = new ArticlesController();
        $controller->registerHook('before', 'beforeFilter');
        $controller->registerHook('before', 'doSomething');
        $this->assertTrue($controller->triggerHook('before', [true]));
        $this->assertFalse($controller->triggerHook('before', [false]));
    }

    public function testTriggerCantBeStopped(): void
    {
        $controller = new ArticlesController();
        $controller->registerHook('before', 'beforeFilter');
        $controller->registerHook('before', 'doSomething');
        $this->assertTrue($controller->triggerHook('before', [false], false));
    }
}
