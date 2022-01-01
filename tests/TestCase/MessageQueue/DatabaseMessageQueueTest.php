<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use PDO;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\MessageQueue\Message;
use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\MessageQueueFixture;

use Lightning\MessageQueue\DatabaseMessageQueue;

final class DatabaseMessageQueueTest extends TestCase
{
    protected $fixtureManager;
    private PDO $pdo;

    public function setUp(): void
    {
        $this->pdo = new PDO(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'));

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([MessageQueueFixture::class]);
    }

    private function createMessageQueue(): DatabaseMessageQueue
    {
        return new DatabaseMessageQueue($this->pdo, 'queue');
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
        $queue = $this->createMessageQueue();
        $queue->send('default', new Message('foo'));

        $this->assertInstanceOf(Message::class, $queue->receive('default'));
        $this->assertNull($queue->receive('default'));
    }

    /**
     * @depends testSend
     */
    public function testReceiveDelayed()
    {
        $queue = $this->createMessageQueue();
        $queue->send('delayed', new Message('foo'), 1);
        $this->assertNull($queue->receive('delayed'));
        sleep(1);
        $this->assertInstanceOf(Message::class, $queue->receive('delayed'));
        $this->assertNull($queue->receive('delayed'));
    }
}
