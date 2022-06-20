<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use Nyholm\Psr7\Response;
use Lightning\Router\Route;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

use Lightning\Autowire\Autowire;
use Lightning\Utility\RandomString;
use Psr\Http\Message\ResponseInterface;
use Lightning\Router\ControllerInterface;
use Lightning\TestSuite\TestRequestHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Router\Middleware\DispatcherMiddleware;

class Foo
{
}

class PostsController implements ControllerInterface
{
    protected ResponseInterface $response;
    protected ServerRequestInterface $request;

    public function beforeFilter(ServerRequestInterface $request): ?ResponseInterface
    {
        $this->setRequest($request->withAttribute('beforeFilter', true));

        return null;
    }

    public function afterFilter(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->setRequest($this->request->withAttribute('afterFilter', true));

        return $response;
    }

    public function index(ServerRequestInterface $serverRequestInterface): ResponseInterface
    {
        $response = new Response();

        $response->getBody()->write('ok');

        return $response;
    }

    public function home(Foo $foo, ServerRequestInterface $serverRequestInterface)
    {
        $response = new Response();

        $response->getBody()->write('foo');

        return $response;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): self
    {
        $this->response = $response;

        return $this;
    }
}

class DummyRequestHandler implements RequestHandlerInterface
{
    /**
    * Handles a request and produces a response.
    *
    * May call other collaborating code to generate the response.
    */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(404, [], 'error');
    }
}

final class DispatcherMiddlewareTest extends TestCase
{
    public function testProcess(): void
    {
        $route = new Route('get', '/articles/:id', [new PostsController(),'index']);
        $route->match('GET', '/articles/1234');

        $middleware = new DispatcherMiddleware($route->getCallable());
        $request = new ServerRequest('GET', '/not-relevant');
        $response = $middleware->process($request, new DummyRequestHandler($request));
        $this->assertEquals('ok', (string) $response->getBody());
    }

    public function testAutowiring(): void
    {
        $route = new Route('get', '/articles/:id', function (ServerRequestInterface $request, RandomString $string) {
            return new Response();
        });
        $route->match('GET', '/articles/1234');

        $middleware = new DispatcherMiddleware($route->getCallable(), null, new Autowire());
        $request = new ServerRequest('GET', '/not-relevant');
        $response = $middleware->process($request, new DummyRequestHandler($request));
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * This Middleware produces the actual response so the handler method is never called.
     */
    public function testBeforeFilter(): void
    {
        $controller = new PostsController();

        $dispatcher = new TestRequestHandler(new DispatcherMiddleware([$controller,'index']), new Response());

        $dispatcher->dispatch(new ServerRequest('GET', '/not-relevant'));

        $this->assertTrue($controller->getRequest()->getAttribute('beforeFilter'));
    }

    /**
     * This Middleware produces the actual response so the handler method is never called.
     */
    public function testAfterFilter(): void
    {
        $controller = new PostsController();

        $dispatcher = new TestRequestHandler(new DispatcherMiddleware([$controller,'index']), new Response());

        $dispatcher->dispatch(new ServerRequest('GET', '/not-relevant'));

        $this->assertTrue($controller->getRequest()->getAttribute('beforeFilter'));
    }
}
