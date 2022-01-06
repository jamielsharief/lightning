<?php declare(strict_types=1);

namespace Lightning\Test\Http\Exception;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Lightning\Http\Cookie\Cookie;
use Lightning\Http\Cookie\Cookies;
use Lightning\Http\Cookie\CookieMiddleware;
use Lightning\TestSuite\TestRequestHandler;

final class CookieMiddlewareTest extends TestCase
{
    public function testRequestWasAdded(): void
    {
        $cookies = new Cookies();
        $middleware = new CookieMiddleware($cookies);
        $request = new ServerRequest('GET', '/');
        $request = $request->withCookieParams(['foo' => 'bar']);

        $middleware->process($request, new TestRequestHandler(new Response()));

        $this->assertEquals('bar', $cookies->get('foo'));
    }

    public function testCookieWasWritten(): void
    {
        $cookies = new Cookies();
        $cookies->add(new Cookie('foo', 'bar'));

        $middleware = new CookieMiddleware($cookies);

        $response = $middleware->process(new ServerRequest('GET', '/'), new TestRequestHandler(new Response()));

        $this->assertEquals('foo=bar; path=/', $response->getHeaderLine('Set-Cookie'));
    }
}
