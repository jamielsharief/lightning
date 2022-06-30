<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use PHPUnit\Framework\TestCase;
use Lightning\MessageQueue\Message;
use Lightning\MessageQueue\MessageConsumer;
use Lightning\MessageQueue\MemoryMessageQueue;

class ConsumerTestMessageQueue extends MemoryMessageQueue
{
    /**
     * Sends a message to the message queue
     */
    public function send(string $queue, Message $message, int $delay = 0): bool
    {
        return false;
    }
}

class TestMessageConsumer extends MessageConsumer
{
    protected array $callled = [];

    /**
     * BeforeSend Callback
     */
    protected function AfterReceive(Message $message, string $queue): void
    {
        parent::afterReceive($message, $queue);
        $this->callled[] = 'afterReceive';
    }

    public function wasCalled(string $method): bool
    {
        return in_array($method, $this->callled);
    }
}

final class MessageConsumerTest extends TestCase
{
    public function testGetMessageQueue(): void
    {
        $messageQueue = new MemoryMessageQueue();
        $consumer = new MessageConsumer($messageQueue, 'default');

        $this->assertEquals($messageQueue, $consumer->getMessageQueue());
    }

    public function testSetMessageQueue(): void
    {
        $messageQueue = new MemoryMessageQueue();
        $messageQueue2 = new ConsumerTestMessageQueue();
        $consumer = new MessageConsumer($messageQueue, 'default');

        $this->assertEquals($messageQueue2, $consumer->setMessageQueue($messageQueue2)->getMessageQueue());
    }

    public function testReceiveNextMessage(): void
    {
        $messageQueue = new MemoryMessageQueue();
        $consumer = new TestMessageConsumer($messageQueue, 'default');
        $this->assertNull($consumer->receive());
        $this->assertFalse($consumer->wasCalled('afterReceive'));
    }

    public function testReceiveNextMessageWithResult(): void
    {
        $messageQueue = new MemoryMessageQueue();
        $messageQueue->send('default', new Message('foo'));

        $consumer = new TestMessageConsumer($messageQueue, 'default');
        $this->assertInstanceOf(Message::class, $consumer->receive());
        $this->assertTrue($consumer->wasCalled('afterReceive'));
    }
}
