<?php declare(strict_types=1);

namespace Lightning\Test\Utility;

use PHPUnit\Framework\TestCase;
use Lightning\Utility\RandomString;

final class RandomStringTest extends TestCase
{
    public function testGetSetCharset(): void
    {
        $this->assertEquals('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', (new RandomString())->getCharset());
        $this->assertEquals('foo', (new RandomString('foo'))->getCharset());
        $this->assertEquals('foo', (new RandomString())->setCharset('foo')->getCharset());
    }

    public function testWithCharset(): void
    {
        $random = new RandomString('foo');
        $this->assertEquals('bar', $random->withCharset('bar')->getCharset());
        $this->assertEquals('foo', $random->getCharset());
    }

    public function testGenerate(): void
    {
        $this->assertMatchesRegularExpression(
            '/^[0123456789abcdef]{32}$/',
             (new RandomString())->withCharset(Randomstring::HEX)->generate(32)
        );

        $this->assertMatchesRegularExpression(
            '/^[0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ]{128}$/',
            (new RandomString())->withCharset(RandomString::BASE_62)->generate(128)
        );
    }
}
