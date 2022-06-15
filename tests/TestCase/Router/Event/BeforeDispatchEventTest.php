<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Router\Event\BeforeDispatchEvent;

final class BeforeDispatchEventTest extends TestCase
{
    private function createEvent(): BeforeDispatchEvent
    {
        return new BeforeDispatchEvent(new ServerRequest('GET', '/not-relevant'));
    }

    public function testGetRequest(): void
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $this->createEvent()->getRequest());
    }

    public function testSetRequest(): void
    {
        $event = $this->createEvent();
        $request = new ServerRequest('GET', '/not-relevant');
        $this->assertInstanceOf(BeforeDispatchEvent::class, $event->setRequest($request));
        $this->assertEquals($request, $event->getRequest());
    }

    public function testGetResponse(): void
    {
        $event = $this->createEvent();
        $this->assertNull($event->getResponse());
        $this->assertInstanceOf(ResponseInterface::class, $event->setResponse(new Response(302))->getResponse());
    }

    public function testSetResponse(): void
    {
        $event = $this->createEvent();
        $response = new Response(302);
        $this->assertInstanceOf(BeforeDispatchEvent::class, $event->setResponse($response));
        $this->assertEquals($response, $event->getResponse());
    }
}
