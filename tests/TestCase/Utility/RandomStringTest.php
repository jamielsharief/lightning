<?php declare(strict_types=1);

namespace Lightning\Test\Utility;

use PHPUnit\Framework\TestCase;
use Lightning\Utility\RandomString;

final class RandomStringTest extends TestCase
{
    public function testGetSetCharacterSet(): void
    {
        $this->assertEquals('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', (new RandomString())->getCharacterSet());
        $this->assertEquals('foo', (new RandomString('foo'))->getCharacterSet());
        $this->assertEquals('foo', (new RandomString())->setCharacterSet('foo')->getCharacterSet());
    }

    public function testWithCharacterSet(): void
    {
        $random = new RandomString('foo');
        $this->assertEquals('bar', $random->withCharacterSet('bar')->getCharacterSet());
        $this->assertEquals('foo', $random->getCharacterSet());
    }

    public function testGenerate(): void
    {
        $this->assertMatchesRegularExpression(
            '/^[0123456789abcdef]{32}$/',
             (new RandomString())->withCharacterSet(Randomstring::HEX)->generate(32)
        );

        $this->assertMatchesRegularExpression(
            '/^[0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ]{128}$/',
            (new RandomString())->withCharacterSet(RandomString::BASE_62)->generate(128)
        );
    }
}
