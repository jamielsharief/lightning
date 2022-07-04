<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use PHPUnit\Framework\TestCase;
use Lightning\MessageQueue\MemoryMessageQueue;

class MemoryMessage
{
    public function __construct(protected string $body)
    {
    }

    public function getBody(): string
    {
        return $this->body;
    }
}

final class MemoryMessageQueueTest extends TestCase
{
    private function createMessageQueue(): MemoryMessageQueue
    {
        return new MemoryMessageQueue();
    }

    public function testSend()
    {
        $this->assertTrue(
            $this->createMessageQueue()->send('default', 'foo')
        );
    }

    /**
     * @depends testSend
     */
    public function testReceive()
    {
        $queue = $this->createMessageQueue();
        $queue->send('default', 'foo');

        $this->assertEquals('foo', $queue->receive('default'));
        $this->assertNull($queue->receive('default'));
    }

    public function testReceiveWithDelay()
    {
        $queue = $this->createMessageQueue();
        $queue->send('default', 'foo', 1);
        $queue->send('default', 'bar');

        $this->assertEquals('bar', $queue->receive('default'));
        $this->assertNull($queue->receive('default'));
        sleep(1);
        $this->assertEquals('foo', $queue->receive('default'));
    }
}
