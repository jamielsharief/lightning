<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Router\Event\AfterDispatchEvent;

final class AfterDispatchEventTest extends TestCase
{
    private function createEvent(): AfterDispatchEvent
    {
        $request = new ServerRequest('GET', '/not-relevant');
        $response = new Response(302);

        return new AfterDispatchEvent($request, $response);
    }

    public function testGetRequest(): void
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $this->createEvent()->getRequest());
    }

    public function testSetRequest(): void
    {
        $event = $this->createEvent();
        $request = new ServerRequest('GET', '/not-relevant');
        $this->assertInstanceOf(AfterDispatchEvent::class, $event->setRequest($request));
        $this->assertEquals($request, $event->getRequest());
    }

    public function testGetResponse(): void
    {
        $this->assertInstanceOf(ResponseInterface::class, $this->createEvent()->getResponse());
    }

    public function testSetResponse(): void
    {
        $event = $this->createEvent();
        $response = new Response(302);
        $this->assertInstanceOf(AfterDispatchEvent::class, $event->setResponse($response));
        $this->assertEquals($response, $event->getResponse());
    }
}
