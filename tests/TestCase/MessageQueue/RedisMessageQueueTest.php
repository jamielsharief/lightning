<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use Redis;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;

use Lightning\MessageQueue\RedisMessageQueue;

class RedisMessage
{
    public function __construct(protected string $body)
    {
    }

    public function getBody(): string
    {
        return $this->body;
    }
}

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
            $this->createMessageQueue()->send('default', 'foo')
        );
    }

    public function testReceive()
    {
        $queue = $this->createMessageQueue();

        $this->assertEquals('foo', $queue->receive('default'));
        $this->assertNull($queue->receive('default'));
    }

    public function testReceiveDelayed()
    {
        $queue = $this->createMessageQueue();
        $queue->send('scheduled', 'foo', 1);
        $this->assertNull($queue->receive('scheduled'));
        sleep(1);
        $this->assertEquals('foo', $queue->receive('scheduled'));
        $this->assertNull($queue->receive('scheduled'));
    }
}
