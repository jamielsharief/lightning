<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Lightning\Router\Event\AfterFilterEvent;
use Psr\Http\Message\ServerRequestInterface;
use Psr\EventDispatcher\StoppableEventInterface;

final class AfterFilterEventTest extends TestCase
{
    public function createEvent(): AfterFilterEvent
    {
        return new AfterFilterEvent(new ServerRequest('GET', '/articles/index'), new Response());
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
           ResponseInterface::class, $this->createEvent()->getResponse()
        );
    }

    public function testStoppableEvent(): void
    {
        $this->assertNotInstanceOf(StoppableEventInterface::class, $this->createEvent());
    }
}
