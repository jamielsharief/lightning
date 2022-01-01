<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\TestSuite\ServerRequestFactory;

final class ServerRequestFactoryTest extends TestCase
{
    public function testGet()
    {
        $serverRequestFactory = new ServerRequestFactory(new Psr17Factory());

        $serverRequest = $serverRequestFactory->create(
            'GET', 'https://www.example.com/articles/index?foo=bar'
        );

        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
    }
}
