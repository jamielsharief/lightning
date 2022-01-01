<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use PHPUnit\Framework\TestCase;
use Lightning\MessageQueue\Message;
use Lightning\MessageQueue\MemoryMessageQueue;

final class MemoryMessageQueueTest extends TestCase
{
    private function createMessageQueue(): MemoryMessageQueue
    {
        return new MemoryMessageQueue();
    }

    public function testSend()
    {
        $this->assertTrue(
            $this->createMessageQueue()->send('default', new Message('foo'))
        );
    }

    /**
     * @depends testSend
     */
    public function testReceive()
    {
        $message = new Message('foo');
        $queue = $this->createMessageQueue();
        $queue->send('default', $message);

        $this->assertInstanceOf(Message::class, $queue->receive('default'));
        $this->assertNull($queue->receive('default'));
    }
}
