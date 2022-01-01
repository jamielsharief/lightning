<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use Redis;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\MessageQueue\Message;

use Lightning\MessageQueue\RedisMessageQueue;

final class RedisMessageQueueTest extends TestCase
{
    private function createMessageQueue(): RedisMessageQueue
    {
        $redis = new Redis();
        $host = env('REDIS_HOST') ?: '127.0.0.1';
        $port = env('REDIS_PORT') ?: 6379;
        $redis->pconnect($host, (int) $port);

        return new RedisMessageQueue($redis);
    }

    public function testSend()
    {
        $this->assertTrue(
            $this->createMessageQueue()->send('default', new Message('foo'))
        );
    }

    public function testReceive()
    {
        $queue = $this->createMessageQueue();

        $this->assertEquals('foo', $queue->receive('default')->getBody());
        $this->assertNull($queue->receive('default'));
    }

    public function testReceiveDelayed()
    {
        $queue = $this->createMessageQueue();
        $queue->send('scheduled', new Message('foo'), 1);
        $this->assertNull($queue->receive('scheduled'));
        sleep(1);
        $this->assertInstanceOf(Message::class, $queue->receive('scheduled'));
        $this->assertNull($queue->receive('scheduled'));
    }
}
