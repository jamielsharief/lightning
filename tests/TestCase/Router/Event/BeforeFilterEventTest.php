<?php declare(strict_types=1);

namespace Lightning\Test\Controller\Event;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Router\Event\BeforeFilterEvent;
use Psr\EventDispatcher\StoppableEventInterface;

final class BeforeFilterEventTest extends TestCase
{
    public function createEvent(): BeforeFilterEvent
    {
        return new BeforeFilterEvent(new ServerRequest('GET', '/articles/index'));
    }

    public function testGetRequest(): void
    {
        $this->assertInstanceOf(
           ServerRequestInterface::class, $this->createEvent()->getRequest()
        );
    }

    public function testSetRequest(): void
    {
        $request = new ServerRequest('GET', '/home');
        $this->assertEquals($request, $this->createEvent()->setRequest($request)->getRequest());
    }

    public function testGetResponse(): void
    {
        $this->assertInstanceOf(
            ResponseInterface::class, $this->createEvent()->setResponse(new Response())->getResponse()
         );
    }

    public function testStoppableEvent(): void
    {
        $event = $this->createEvent();
        $this->assertInstanceOf(StoppableEventInterface::class, $event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertTrue($event->stop()->isPropagationStopped());
    }

    public function testGetResponseNull(): void
    {
        $this->assertNull($this->createEvent()->getResponse());
    }
}
