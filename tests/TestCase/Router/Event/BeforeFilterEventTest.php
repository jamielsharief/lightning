<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Router\Event\BeforeFilterEvent;

final class BeforeFilterEventTest extends TestCase
{
    public function testGetRequest(): void
    {
        $request = new ServerRequest('GET', '/not-relevant');
        $response = new Response(302);

        $event = new BeforeFilterEvent($request, $response);

        $this->assertInstanceOf(ServerRequestInterface::class, $event->getRequest());
    }
}
