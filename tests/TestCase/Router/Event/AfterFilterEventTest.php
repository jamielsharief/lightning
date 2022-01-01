<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Lightning\Router\Event\AfterFilterEvent;
use Psr\Http\Message\ServerRequestInterface;

final class AfterFilterEventTest extends TestCase
{
    private function createEvent(): AfterFilterEvent
    {
        $request = new ServerRequest('GET', '/not-relevant');
        $response = new Response(302);

        return new AfterFilterEvent($request, $response);
    }
    public function testGetRequest(): void
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $this->createEvent()->getRequest());
    }

    public function testGetResponse(): void
    {
        $this->assertInstanceOf(ResponseInterface::class, $this->createEvent()->getResponse());
    }
}
