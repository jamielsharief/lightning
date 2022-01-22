<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Controller\Event;

use Lightning\View\View;
use Nyholm\Psr7\Response;

use Nyholm\Psr7\ServerRequest;

use PHPUnit\Framework\TestCase;
use Lightning\View\ViewCompiler;
use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\AbstractController;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Controller\Event\AbstractControllerEvent;
use Lightning\Test\TestCase\Controller\TestApp\ArticlesController;

abstract class AbstractControllerEventTestCase extends TestCase
{
    public function testGetRequest(): void
    {
        $event = $this->createEvent();
        $this->assertNull($event->getRequest());
        $event->setRequest(new ServerRequest('GET', '/not-relevant'));
        $this->assertInstanceOf(ServerRequestInterface::class, $event->getRequest());
    }

    public function testSetRequest(): void
    {
        $event = $this->createEvent();
        $request = new ServerRequest('GET', '/not-relevant');
        $this->assertInstanceOf(AbstractControllerEvent::class, $event->setRequest($request));
        $this->assertEquals($request, $event->getRequest());
    }

    public function testGetResponse(): void
    {
        $event = $this->createEvent();
        $this->assertNull($event->getResponse());
        $event->setResponse(new Response(302));
        $this->assertInstanceOf(ResponseInterface::class, $event->getResponse());
    }

    public function testSetResponse(): void
    {
        $event = $this->createEvent();
        $response = new Response(302);
        $this->assertInstanceOf(AbstractControllerEvent::class, $event->setResponse($response));
        $this->assertEquals($response, $event->getResponse());
    }

    public function testGetController(): void
    {
        $this->assertInstanceOf(AbstractController::class, $this->createEvent()->getController());
    }

    abstract protected function createEvent(): AbstractControllerEvent;

    protected function createController(): AbstractController
    {
        $path = __DIR__ .'/TestApp/templates';

        return  new ArticlesController(
            new Response(), new View(new ViewCompiler($path, sys_get_temp_dir()), $path)
        );
    }
}
