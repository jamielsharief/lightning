<?php declare(strict_types=1);

namespace Lightning\Test\Http\Session;

use Nyholm\Psr7\Response;

use Nyholm\Psr7\ServerRequest;

use PHPUnit\Framework\TestCase;
use Lightning\Http\Session\PhpSession;
use Lightning\TestSuite\TestRequestHandler;
use Lightning\Http\Session\Middleware\SessionMiddleware;

final class SessionMiddlewareTest extends TestCase
{
    public function testStartAndStop(): void
    {
        $session = new PhpSession();
        $middleware = new SessionMiddleware($session);
        $handler = new TestRequestHandler($middleware, new Response());

        $handler->beforeHandle(function () use ($session) {
            $this->assertTrue($session->isStarted());
        });

        $response = $handler->dispatch(new ServerRequest('GET', '/'));

        $this->assertFalse($session->isStarted());
    }

    public function testReadSessionFromCookie(): void
    {
        $session = new PhpSession();
        $middleware = new SessionMiddleware($session);
        $handler = new TestRequestHandler($middleware, new Response());

        $request = (new ServerRequest('GET', '/'))->withCookieParams(['id' => '123456789']);
        $response = $handler->dispatch($request);

        $this->assertEquals('123456789', $session->getId());
    }

    public function testAddCookie(): void
    {
        $session = new PhpSession();
        $middleware = new SessionMiddleware($session);
        $handler = new TestRequestHandler($middleware, new Response());

        $request = (new ServerRequest('GET', '/'))->withCookieParams(['id' => '123456789']);
        $response = $handler->dispatch($request);
        $this->assertEquals('id=123456789; max-age=900; path=/; samesite=Lax; httponly', $response->getHeaderLine('Set-Cookie'));
    }

    public function testDeleteCookie(): void
    {
        $session = new PhpSession();
        $middleware = new SessionMiddleware($session);
        $handler = new TestRequestHandler($middleware, new Response());

        $request = (new ServerRequest('GET', '/'))->withCookieParams(['id' => '123456789']);

        $handler->beforeHandle(function () use ($session) {
            $session->destroy();
        });

        $response = $handler->dispatch($request);
        $this->assertEquals('id=deleted; max-age=-1; path=/; samesite=Lax; httponly', $response->getHeaderLine('Set-Cookie'));
    }
}
