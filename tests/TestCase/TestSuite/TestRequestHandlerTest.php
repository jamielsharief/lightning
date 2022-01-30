<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Lightning\TestSuite\TestRequestHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DummyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request->withAttribute('ok', true));

        $response->getBody()->write('html');

        return $response;
    }
}

final class TestRequestHandlerTest extends TestCase
{
    public function testDispatch(): void
    {
        $handler = new TestRequestHandler(new DummyMiddleware(), new Response());
        $this->assertInstanceOf(ResponseInterface::class, $handler->dispatch(new ServerRequest('GET', '/')));
    }

    public function testHandleBefore(): void
    {
        $handler = new TestRequestHandler(new DummyMiddleware(), new Response());
        $handler->beforeHandle(function (ServerRequestInterface $request) {
            $this->assertTrue($request->getAttribute('ok'));
        });
        $handler->dispatch(new ServerRequest('GET', '/'));
    }

    public function testHandle(): void
    {
        $handler = new TestRequestHandler(new DummyMiddleware(), new Response());

        $response = $handler->dispatch(new ServerRequest('GET', '/'));
        $this->assertEquals('html', (string) $response->getBody());
    }

    public function testGetRequest(): void
    {
        $handler = new TestRequestHandler(new DummyMiddleware(), new Response());

        $response = $handler->dispatch(new ServerRequest('GET', '/'));

        $this->assertInstanceOf(ServerRequestInterface::class, $handler->getRequest());
        $this->assertTrue($handler->getRequest()->getAttribute('ok'));
    }
}
