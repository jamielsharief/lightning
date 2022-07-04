<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use PHPUnit\Framework\TestCase;
use Lightning\MessageQueue\Message;
use Lightning\MessageQueue\MessageProducer;
use Lightning\MessageQueue\MemoryMessageQueue;

class ProducerMessage
{
    public function __construct(protected string $body)
    {
    }

    public function getBody(): string
    {
        return $this->body;
    }
}

class ProducerTestMessageQueue extends MemoryMessageQueue
{

    public function send(string $queue, string $message, int $delay = 0): bool
    {
        return false;
    }
}

final class MessageProducerTest extends TestCase
{
    public function testGetMessageQueue(): void
    {
        $messageQueue = new MemoryMessageQueue();
        $producer = new MessageProducer($messageQueue);

        $this->assertEquals($messageQueue, $producer->getMessageQueue());
    }

    public function testSetMessageQueue(): void
    {
        $messageQueue = new MemoryMessageQueue();
        $messageQueue2 = new ProducerTestMessageQueue();
        $producer = new MessageProducer($messageQueue);

        $this->assertEquals($messageQueue2, $producer->setMessageQueue($messageQueue2)->getMessageQueue());
    }

    public function testSend(): void
    {
        $messageQueue = new MemoryMessageQueue();
        $messageProducer = new MessageProducer($messageQueue);

        $this->assertTrue($messageProducer->send('default', new ProducerMessage('foo')));
        $this->assertNotEmpty($messageQueue->receive('default'));

    }

    public function testSendFail(): void
    {
        $messageQueue = new ProducerTestMessageQueue();
        $messageProducer = new MessageProducer($messageQueue);

        $this->assertFalse($messageProducer->send('default',new ProducerMessage('foo')));
    }
}
