<?php declare(strict_types=1);

namespace Lightning\Test\Controller;

use Lightning\View\View;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Lightning\View\ViewCompiler;

use Lightning\Controller\Event\AfterRenderEvent;
use Lightning\Controller\Event\AfterInitializeEvent;
use Lightning\Controller\Event\AbstractControllerEvent;
use Lightning\Test\TestCase\Controller\TestApp\ArticlesController;

final class ControllerEventTest extends TestCase
{
    public function eventProvider()
    {
        $path = __DIR__ .'/TestApp/templates';
        $controller = new ArticlesController(
            new Response(),
            new View(new ViewCompiler($path, sys_get_temp_dir()), $path),
        );
        $data = [
            'foo' => 'bar'
        ];

        return [
            [new AfterInitializeEvent($controller, $data)],
            [new AfterRenderEvent($controller, $data)],
        ];
    }

    /**
     * @dataProvider eventProvider
     */
    public function testGetController(AbstractControllerEvent $event): void
    {
        $this->assertInstanceOf(ArticlesController::class, $event->getController());
    }
    /**
     * @dataProvider eventProvider
     */
    public function testGetData(AbstractControllerEvent $event): void
    {
        $this->assertEquals(['foo' => 'bar'], $event->getData());
    }
}
