<?php declare(strict_types=1);

namespace Lightning\Test\Http\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\Http\Cookie\Cookie;

final class CookieTest extends TestCase
{
    public function testInvalidName()
    {
        $regex = '#[\s\t\(\)\[\]<>@,;:?="/\\\]#';

        // some quick sanity checks
        $this->assertMatchesRegularExpression($regex, 'foo bar');
        $this->assertMatchesRegularExpression($regex, 'foo  bar');
        $this->assertMatchesRegularExpression($regex, 'foo=bar');
        $this->assertMatchesRegularExpression($regex, 'foo,bar');
        $this->assertMatchesRegularExpression($regex, 'foo;bar');
        $this->assertMatchesRegularExpression($regex, 'foo[bar]');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid cookie name `foo bar`');

        new Cookie('foo bar', '');
    }
    public function testGetName(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals('foo', $cookie->getName());
    }

    public function testValue(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals('bar', $cookie->getValue());

        $this->assertEquals('', $cookie->setValue('')->getValue());
    }

    public function testMaxAge(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals(0, $cookie->getMaxAge());
        $this->assertEquals(3600, $cookie->setMaxAge(3600)->getMaxAge());
    }

    public function testHttpOnly(): void
    {
        $cookie = new Cookie('foo', 'bar');

        $this->assertFalse($cookie->isHttpOnly());
        $this->assertInstanceOf(Cookie::class, $cookie->setHttpOnly(true));
        $this->assertTrue($cookie->isHttpOnly());
    }

    public function testSecure(): void
    {
        $cookie = new Cookie('foo', 'bar');

        $this->assertFalse($cookie->getSecure());
        $this->assertInstanceOf(Cookie::class, $cookie->setSecure(true));
        $this->assertTrue($cookie->getSecure());
    }

    public function testPath(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals('/', $cookie->getPath());
        $this->assertEquals('', $cookie->setPath('')->getPath());
    }

    public function testDomain(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals('/', $cookie->getPath());
        $this->assertEquals('', $cookie->setPath('')->getPath());
    }

    public function testSetSameSite(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertInstanceOf(Cookie::class, $cookie->setSameSite('Lax'));
        $this->assertEquals('Lax', $cookie->getSameSite());
    }

    public function testToString(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals('foo=bar; path=/', $cookie->toString());
        $this->assertEquals('foo=bar; path=/', (string) $cookie);
    }

    public function testToStringDomain(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals('foo=bar; path=/', (string) $cookie); // no domain

        $cookie->setDomain('example.com');
        $this->assertEquals('foo=bar; path=/; domain=example.com', (string) $cookie);
    }

    public function testToStringHttpOnly(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals('foo=bar; path=/', (string) $cookie); // no domain

        $cookie->setHttpOnly(true);
        $this->assertEquals('foo=bar; path=/; httponly', (string) $cookie); // no domain
    }
    public function testToSecure(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals('foo=bar; path=/', (string) $cookie); // no domain

        $cookie->setSecure(true);
        $this->assertEquals('foo=bar; path=/; secure', (string) $cookie); // no domain
    }

    public function testToSameSite(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals('foo=bar; path=/', (string) $cookie); // no domain

        $cookie->setSameSite('Lax');
        $this->assertEquals('foo=bar; path=/; samesite=Lax', (string) $cookie); // no domain
    }
}
