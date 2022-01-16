<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use Nyholm\Psr7\Response;
use Lightning\Router\Route;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
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

        $middleware = new DispatcherMiddleware($route->getCallable(), $route->getVariables());
        $request = new ServerRequest('GET', '/not-relevant');
        $response = $middleware->process($request, new DummyRequestHandler($request));
        $this->assertEquals('ok', (string) $response->getBody());
    }

    public function testProcessEventsWereCalled(): void
    {
        $route = new Route('get', '/articles/:id', [new PostsController(),'index']);
        $route->match('GET', '/articles/1234');

        $eventDispatcher = new TestEventDispatcher();
        $middleware = new DispatcherMiddleware($route->getCallable(), $route->getVariables(), $eventDispatcher);
        $request = new ServerRequest('GET', '/not-relevant');
        $response = $middleware->process($request, new DummyRequestHandler($request));
        $this->assertEquals([BeforeFilterEvent::class,AfterFilterEvent::class], $eventDispatcher->getDispatchedEvents());
    }

    public function testProcessAddedArgs(): void
    {
        $route = new Route('GET', '/articles/:id', [new PostsController(),'index'], ['id' => '[0-9]+']);
        $route->match('GET', '/articles/1234');

        $eventDispatcher = new TestEventDispatcher();
        $middleware = new DispatcherMiddleware($route->getCallable(), $route->getVariables(), $eventDispatcher);
        $request = new ServerRequest('GET', '/articles/1234');
        $response = $middleware->process($request, new DummyRequestHandler($request));

        // Pull the request from the Event object
        $event = $eventDispatcher->getDispatchedEvent(BeforeFilterEvent::class);
        $this->assertEquals('1234', $event->getRequest()->getAttribute('id'));
    }
}
