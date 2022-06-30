<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use PHPUnit\Framework\TestCase;
use Lightning\MessageQueue\Message;
use Lightning\MessageQueue\MessageProducer;
use Lightning\MessageQueue\MemoryMessageQueue;

class ProducerTestMessageQueue extends MemoryMessageQueue
{
    /**
     * Sends a message to the message queue
     */
    public function send(string $queue, Message $message, int $delay = 0): bool
    {
        return false;
    }
}

class TestMessageProducer extends MessageProducer
{
    protected array $callled = [];

    /**
     * BeforeSend Callback
     */
    protected function beforeSend(Message $message): void
    {
        parent::beforeSend($message);
        $this->callled[] = 'beforeSend';
    }

    /**
     * AfterSend Callback
     */
    protected function afterSend(Message $message): void
    {
        parent::afterSend($message);
        $this->callled[] = 'afterSend';
    }

    public function wasCalled(string $method): bool
    {
        return in_array($method, $this->callled);
    }
}

final class MessageProducerTest extends TestCase
{
    public function testGetMessageQueue(): void
    {
        $messageQueue = new MemoryMessageQueue();
        $producer = new MessageProducer($messageQueue, 'default');

        $this->assertEquals($messageQueue, $producer->getMessageQueue());
    }

    public function testSetMessageQueue(): void
    {
        $messageQueue = new MemoryMessageQueue();
        $messageQueue2 = new ProducerTestMessageQueue();
        $producer = new MessageProducer($messageQueue, 'default');

        $this->assertEquals($messageQueue2, $producer->setMessageQueue($messageQueue2)->getMessageQueue());
    }

    public function testSend(): void
    {
        $messageQueue = new MemoryMessageQueue();

        $messageProducer = new TestMessageProducer($messageQueue, 'default');

        $this->assertTrue($messageProducer->send(new Message('foo')));

        $this->assertTrue($messageProducer->wasCalled('beforeSend'));
        $this->assertTrue($messageProducer->wasCalled('afterSend'));
    }

    public function testSendFail(): void
    {
        $messageQueue = new ProducerTestMessageQueue();

        $messageProducer = new TestMessageProducer($messageQueue, 'default');

        $this->assertFalse($messageProducer->send(new Message('foo')));
        $this->assertTrue($messageProducer->wasCalled('beforeSend'));
        $this->assertFalse($messageProducer->wasCalled('afterSend'));
    }
}
