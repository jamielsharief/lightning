<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use PHPUnit\Framework\TestCase;
use Lightning\MessageQueue\Message;

final class MessageTest extends TestCase
{
    public function testGetBody(): void
    {
        $this->assertEquals(
            'foo',
            (new Message('foo'))->getBody()
        );
    }

    public function testGetId(): void
    {
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{32}/',
            (new Message('foo'))->getId()
        );
    }

    public function testGetTimestamp(): void
    {
        $this->assertEquals(
            time(),
            (new Message('foo'))->getTimestamp()
        );
    }
}
