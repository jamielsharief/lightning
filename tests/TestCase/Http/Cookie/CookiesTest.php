<?php declare(strict_types=1);

namespace Lightning\Test\Http\Exception;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Lightning\Http\Cookie\Cookie;
use Lightning\Http\Cookie\Cookies;
use Psr\Http\Message\ResponseInterface;

final class CookiesTest extends TestCase
{
    public function testGet(): void
    {
        $cookies = new Cookies(['foo' => 'bar']);
        $this->assertNull($cookies->get('bar'));
        $this->assertEquals('bar', $cookies->get('foo'));
        $this->assertEquals('xxx', $cookies->get('bar', 'xxx'));
    }

    public function testHas(): void
    {
        $cookies = new Cookies(['foo' => 'bar']);
        $this->assertTrue($cookies->has('foo'));
        $this->assertFalse($cookies->has('bar'));
    }

    public function testDelete(): void
    {
        $response = new Response();
        $cookies = new Cookies();
        $cookies->delete(new Cookie('foo'));

        $response = $cookies->addToResponse($response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('foo=; max-age=-1; path=/', $response->getHeaderLine('Set-Cookie'));
    }

    public function testAddToResponse(): void
    {
        $cookies = new Cookies(['abc' => '1234']);

        $response = new Response();

        $cookie = new Cookie('foo', 'bar');
        $cookies->add($cookie);

        $response = $cookies->addToResponse($response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('foo=bar; path=/', $response->getHeaderLine('Set-Cookie'));
    }

    public function testGetIterator(): void
    {
        $cookies = new Cookies(['abc' => '1234']);
        $this->assertEquals(['abc' => '1234'], iterator_to_array($cookies));
    }

    public function testCount(): void
    {
        $this->assertCount(0, new Cookies());
        $this->assertCount(1, new Cookies(['foo' => 'bar']));
        $this->assertCount(2, new Cookies(['abc' => '1234','foo' => 'bar']));
    }

    public function testSetServerRequest(): void
    {
        $cookies = new Cookies();
        $request = new ServerRequest('GET', '/articles');
        $request = $request->withCookieParams(['foo' => 'bar']);

        $this->assertEquals('bar', $cookies->setServerRequest($request)->get('foo'));
    }
}
