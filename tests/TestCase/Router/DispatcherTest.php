<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Lightning\Router\RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Router\Exception\RouterException;

class ResponseCreatorMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new Response();
    }
}

class RedMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write('red');

        return $response;
    }
}

class YellowMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write('yellow');

        return $response;
    }
}

class GreenMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write('green');

        return $response;
    }
}

final class DispatcherTest extends TestCase
{
    public function testDispatchNoResponse(): void
    {
        $requestHandler = new RequestHandler();

        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('No Middleware');
        $requestHandler->handle(new ServerRequest('GET', '/articles'));
    }

    public function testDispatch(): void
    {
        $requestHandler = new RequestHandler([
            new ResponseCreatorMiddleware()
        ]);

        $this->assertInstanceOf(ResponseInterface::class, $requestHandler->handle(new ServerRequest('GET', '/articles')));
    }

    public function testMiddleware(): void
    {
        $requestHandler = new RequestHandler([
            new RedMiddleware(),
            new ResponseCreatorMiddleware()
        ]);

        $response = $requestHandler->handle(new ServerRequest('GET', '/articles'));

        $this->assertStringContainsString('red', (string) $response->getBody());
    }

    public function testMiddlewares(): void
    {
        $requestHandler = new RequestHandler([
            new RedMiddleware(),
            new GreenMiddleware(),
            new YellowMiddleware(),
            new ResponseCreatorMiddleware()
        ]);

        $response = $requestHandler->handle(new ServerRequest('GET', '/articles'));

        $this->assertStringContainsString('yellowgreenred', (string) $response->getBody());
    }
}
