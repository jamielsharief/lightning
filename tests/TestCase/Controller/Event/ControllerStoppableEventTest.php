<?php declare(strict_types=1);

namespace Lightning\Test\Controller;

use Lightning\View\View;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Lightning\View\ViewCompiler;
use Lightning\Controller\Event\BeforeRenderEvent;
use Lightning\Controller\Event\BeforeRedirectEvent;
use Lightning\Controller\Event\AbstractControllerStoppableEvent ;
use Lightning\Test\TestCase\Controller\TestApp\ArticlesController;

final class ControllerStoppableEventTest extends TestCase
{
    public function eventProvider()
    {
        $path = __DIR__ .'/TestApp/templates';
        $controller = new ArticlesController(
            new Response(),
            new View(new ViewCompiler($path, sys_get_temp_dir()), $path)
        );
        $data = [
            'foo' => 'bar'
        ];

        return [
            [new BeforeRenderEvent($controller, $data)],
            [new BeforeRedirectEvent($controller, $data)],
        ];
    }

    /**
     * @dataProvider eventProvider
     */
    public function testGetController(AbstractControllerStoppableEvent $event): void
    {
        $this->assertInstanceOf(ArticlesController::class, $event->getController());
    }
    /**
     * @dataProvider eventProvider
     */
    public function testGetData(AbstractControllerStoppableEvent $event): void
    {
        $this->assertEquals(['foo' => 'bar'], $event->getData());
    }
    /**
     * @dataProvider eventProvider
     */
    public function testStop(AbstractControllerStoppableEvent $event): void
    {
        $this->assertFalse($event->isPropagationStopped());
        $event->stop();
        $this->assertTrue($event->isPropagationStopped());
    }
}
