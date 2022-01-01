<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use BadMethodCallException;
use Lightning\Router\Route;
use PHPUnit\Framework\TestCase;

class FakeController
{
    public function index()
    {
    }
}

class InvokeableController
{
    public function __invoke()
    {
    }
}

final class RouteTest extends TestCase
{
    public function testMatch(): void
    {
        $route = new Route('GET', '/articles', 'App\Controller\ArticlesController::index');
        $this->assertTrue($route->match('GET', '/articles'));
    }

    public function testNotMatchMethod(): void
    {
        $route = new Route('GET', '/articles', 'App\Controller\ArticlesController::index');
        $this->assertFalse($route->match('POST', '/articles'));
    }

    public function testNotMatchURI(): void
    {
        $route = new Route('GET', '/articles', 'App\Controller\ArticlesController::index');
        $this->assertFalse($route->match('GET', '/'));
    }

    public function testMatchVars(): void
    {
        $route = new Route('GET', '/articles/:id/:category', 'App\Controller\ArticlesController::index');
        $this->assertTrue($route->match('GET', '/articles/1234/new'));

        $this->assertEquals([
            'id' => 1234,
            'category' => 'new'
        ], $route->getVariables());
    }

    public function testMatchConstraints(): void
    {
        $route = new Route('GET', '/articles/:id', 'App\Controller\ArticlesController::index', ['id' => '[0-9]{3}']);
        $this->assertTrue($route->match('GET', '/articles/123'));
        $this->assertFalse($route->match('GET', '/articles/1234567'));
    }

    public function testGetHandler(): void
    {
        $handler = 'App\Controller\ArticlesController::index';
        $route = new Route('GET', '/articles', $handler);
        $this->assertSame($handler, $route->getHandler());

        $handler = [$this,'testGetHandler'];
        $route = new Route('GET', '/articles', $handler);
        $this->assertSame($handler, $route->getHandler());
    }

    public function testGetPath(): void
    {
        $route = new Route('GET', '/articles', 'App\Controller\ArticlesController::index');
        $this->assertEquals('/articles', $route->getPath());
    }

    public function testGetMethod(): void
    {
        $route = new Route('GET', '/articles', 'App\Controller\ArticlesController::index');
        $this->assertEquals('GET', $route->getMethod());
    }

    public function testGetUri(): void
    {
        $route = new Route('GET', '/articles', 'App\Controller\ArticlesController::index');
        $this->assertNull($route->getUri());

        $route->match('GET', '/articles');
        $this->assertEquals('/articles', $route->getUri());
    }

    public function testInvoke(): void
    {
        $route = new Route('GET', '/fake', 'Lightning\Test\Router\FakeController::index');
        $route->match('GET', '/fake');
        $callable = $route->__invoke();
        $this->assertTrue(is_callable($callable));
    }

    public function testInvokeString(): void
    {
        $route = new Route('GET', '/fake', InvokeableController::class);
        $route->match('GET', '/fake');
        $callable = $route->__invoke();
        $this->assertTrue(is_callable($callable));
    }

    public function testGetCallableNotInvoked(): void
    {
        $route = new Route('GET', '/articles/:id/:category', 'App\Controller\ArticlesController::index');
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Route must be invoked first');
        $route->getCallable();
    }
}
