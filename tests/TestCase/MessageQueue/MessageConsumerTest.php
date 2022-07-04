<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use PHPUnit\Framework\TestCase;
use Lightning\MessageQueue\MessageConsumer;
use Lightning\MessageQueue\MessageProducer;
use Lightning\MessageQueue\MemoryMessageQueue;

class ConsumerMessage
{
    private bool $handled = false;

    public function __construct(protected string $body)
    {
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function handle(): void
    {
        $this->handled = true;
    }

    public function wasHandled(): bool
    {
        return $this->handled;
    }
}

class ConsumerTestMessageQueue extends MemoryMessageQueue
{
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

    public function testReceiveNotWaitNoMessage(): void
    {
        $messageQueue = new MemoryMessageQueue();
        $consumer = new MessageConsumer($messageQueue, 'default');
        $this->assertNull($consumer->receiveNoWait());
    }

    public function testReceiveNoWait(): void
    {
        $messageQueue = new MemoryMessageQueue();

        (new MessageProducer($messageQueue))->send('default', new ConsumerMessage('foo'));

        $consumer = new MessageConsumer($messageQueue, 'default');

        $this->assertInstanceOf(ConsumerMessage::class, $consumer->receiveNoWait());
    }

    public function testReceiveNoWaitHandler(): void
    {
        $messageQueue = new MemoryMessageQueue();

        (new MessageProducer($messageQueue))->send('default', new ConsumerMessage('foo'));

        $consumer = new MessageConsumer($messageQueue, 'default');
        $consumer->setMessageListener([$this,'messageHandler']);

        $message = $consumer->receiveNoWait();

        $this->assertTrue($message->wasHandled());
    }

    public function testStop(): void
    {
        $consumer = new MessageConsumer(new MemoryMessageQueue(), 'default');

        $this->assertInstanceOf(MessageConsumer::class, $consumer->stop());
    }

    public function testReceive(): void
    {
        $messageQueue = new MemoryMessageQueue();

        (new MessageProducer($messageQueue))->send('default', new ConsumerMessage('foo'));

        $consumer = new MessageConsumer($messageQueue, 'default');

        $start = time();
        $consumer->setMessageListener(function (object $message) use ($consumer, $start) {
            if (time() + 2 > $start) {
                $this->assertTrue(true);
                $consumer->stop();
            }
        });

        $consumer->receive();
    }

    public function testReceiveTimeout(): void
    {
        $messageQueue = new MemoryMessageQueue();

        (new MessageProducer($messageQueue))->send('default', new ConsumerMessage('foo'));

        $consumer = new MessageConsumer($messageQueue, 'default');

        $start = time();

        $consumer->receive(2);

        $this->assertEquals($start + 2, time());
    }

    public function messageHandler(ConsumerMessage $message)
    {
        $message->handle();
    }
}
