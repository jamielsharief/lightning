<?php declare(strict_types=1);

namespace Lightning\Test\Controller;

use Lightning\View\View;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Lightning\View\ViewCompiler;
use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\AbstractController;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Controller\Event\AfterRenderEvent;
use Lightning\Test\TestCase\Controller\TestApp\ArticlesController;

final class AfterRenderEventTest extends TestCase
{
    public function testGetController(): void
    {
        $controller = $this->createController();
        $event = new AfterRenderEvent($controller);
        $this->assertInstanceOf(ArticlesController::class, $event->getController());
    }

    public function testGetRequest(): void
    {
        $controller = $this->createController();
        $request = new ServerRequest('GET', '/');

        $event = new AfterRenderEvent($controller, $request);
        $this->assertInstanceOf(ServerRequestInterface::class, $event->getRequest());
    }

    public function testGetRequestNull(): void
    {
        $controller = $this->createController();
        $event = new AfterRenderEvent($controller);
        $this->assertNull($event->getRequest());
    }

    public function testGetResponse(): void
    {
        $controller = $this->createController();
        $request = new ServerRequest('GET', '/');
        $response = new Response();

        $event = new AfterRenderEvent($controller, $request, $response);
        $this->assertInstanceOf(ResponseInterface::class, $event->getResponse());
    }

    public function testGetResponseNull(): void
    {
        $controller = $this->createController();
        $event = new AfterRenderEvent($controller);
        $this->assertNull($event->getResponse());
    }

    private function createController(): AbstractController
    {
        $path = __DIR__ .'/TestApp/templates';

        return  new ArticlesController(
            new Response(), new View(new ViewCompiler($path, sys_get_temp_dir()), $path)
        );
    }
}
