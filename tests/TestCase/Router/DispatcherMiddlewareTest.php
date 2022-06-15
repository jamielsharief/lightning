<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use Nyholm\Psr7\Response;
use Lightning\Router\Route;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

use Lightning\Autowire\Autowire;
use Lightning\Utility\RandomString;
use Psr\Http\Message\ResponseInterface;
use Lightning\Router\Event\AfterFilterEvent;
use Lightning\TestSuite\TestEventDispatcher;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Router\Event\BeforeFilterEvent;
use Lightning\Router\Middleware\DispatcherMiddleware;

class Foo
{
}

class PostsController
{
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

        $middleware = new DispatcherMiddleware($route->getCallable(), null, null, new Autowire());
        $request = new ServerRequest('GET', '/not-relevant');
        $response = $middleware->process($request, new DummyRequestHandler($request));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testBeforeFilter(): void
    {
        $route = new Route('get', '/articles/:id', function (ServerRequestInterface $request) {
            $this->assertEquals('bar', $request->getAttribute('foo'));

            return new Response(200);
        });
        $route->match('GET', '/articles/1234');
        $eventDispatcher = new TestEventDispatcher();
        $eventDispatcher->on(BeforeFilterEvent::class, function (BeforeFilterEvent $event) {
            $event->setRequest($event->getRequest()->withAttribute('foo', 'bar'));
        });

        $middleware = new DispatcherMiddleware($route->getCallable(), null, $eventDispatcher);
        $request = new ServerRequest('GET', '/not-relevant');
        $response = $middleware->process($request, new DummyRequestHandler($request));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testBeforeFilterChangeResponse(): void
    {
        $route = new Route('get', '/articles/:id', function (ServerRequestInterface $request) {
            return new Response(200);
        });
        $route->match('GET', '/articles/1234');
        $eventDispatcher = new TestEventDispatcher();
        $eventDispatcher->on(BeforeFilterEvent::class, function (BeforeFilterEvent $event) {
            $event->setResponse(new Response(418));
        });

        $middleware = new DispatcherMiddleware($route->getCallable(), null, $eventDispatcher);
        $request = new ServerRequest('GET', '/not-relevant');
        $response = $middleware->process($request, new DummyRequestHandler($request));
        $this->assertEquals(418, $response->getStatusCode());
    }

    public function testAfterFilter(): void
    {
        $route = new Route('get', '/articles/:id', function (ServerRequestInterface $request) {
            return new Response(200);
        });
        $route->match('GET', '/articles/1234');
        $eventDispatcher = new TestEventDispatcher();
        $eventDispatcher->on(AfterFilterEvent::class, function (AfterFilterEvent $event) {
            $event->setResponse(new Response(418));
        });

        $middleware = new DispatcherMiddleware($route->getCallable(), null, $eventDispatcher);
        $request = new ServerRequest('GET', '/not-relevant');
        $response = $middleware->process($request, new DummyRequestHandler($request));
        $this->assertEquals(418, $response->getStatusCode());
    }
}
