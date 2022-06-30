<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue\Command;

use PDO;
use Exception;
use PHPUnit\Framework\TestCase;

use Lightning\MessageQueue\Message;
use Lightning\Fixture\FixtureManager;
use Lightning\MessageQueue\MessageConsumer;
use Lightning\MessageQueue\WorkerInterface;
use Lightning\Console\ConsoleArgumentParser;
use Lightning\Console\TestSuite\TestConsoleIo;
use Lightning\MessageQueue\MemoryMessageQueue;
use Lightning\MessageQueue\MessageQueueInterface;
use Lightning\MessageQueue\Command\QueueWorkerCommand;
use Lightning\Console\TestSuite\ConsoleIntegrationTestTrait;

class Task implements WorkerInterface
{
    public function __construct(protected bool $success = true)
    {
    }

    public function run(): void
    {
        if ($this->success === false) {
            throw new Exception('Something happened');
        }
    }
}

class TestQueueWorkerCommand extends QueueWorkerCommand
{
    private int $count = 0;
    protected function daemon(string $queue): void
    {
        $this->count ++;

        if ($this->count > 3) {
            return ;
        }

        parent::daemon($queue);
    }
}

final class QueueWorkerCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected PDO $pdo;
    protected string $migrationFolder;

    protected FixtureManager $fixtureManager;

    protected MessageQueueInterface $messageQueue;

    public function setUp(): void
    {
        $this->messageQueue = new MemoryMessageQueue();

        $command = new TestQueueWorkerCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new MessageConsumer($this->messageQueue, 'default'));
        $this->setupIntegrationTesting($command);
    }

    public function testExecute(): void
    {
        $this->execute(['default']);
        $this->assertExitSuccess();
    }

    public function testExecuteWithMessage(): void
    {
        $message = new Message(serialize(new Task()));
        $this->messageQueue->send('mailers', $message);
        $this->execute(['mailers']);
        $this->assertExitSuccess();
        $this->assertOutputContains(sprintf('<white>[</white> <green>OK</green> <white>] %s Lightning\Test\TestCase\MessageQueue\Command\Task</white', $message->getId()));
    }

    public function testExecuteError(): void
    {
        $message = new Message(serialize(new Task(false)));
        $this->messageQueue->send('mailers', $message);
        $this->execute(['mailers']);
        $this->assertExitSuccess();
        $this->assertOutputContains(sprintf('<white>[</white> <red>ERROR</red> <white>] %s Lightning\Test\TestCase\MessageQueue\Command\Task</white>', $message->getId()));
    }

    public function testExecuteNonWorker(): void
    {
        $message = new Message('foo');
        $this->messageQueue->send('mailers', $message);
        $this->execute(['mailers']);
        $this->assertExitSuccess();
        $this->assertOutputContains(sprintf('<white>[</white> <yellow>SKIPPED</yellow> <white>] Message with id `%s` is not a worker</white>', $message->getId()));
    }

    public function testExecuteWithMessages(): void
    {
        $message1 = new Message(serialize(new Task()));
        $message2 = new Message(serialize(new Task()));
        $this->messageQueue->send('mailers', $message1);
        $this->messageQueue->send('mailers', $message2);
        $this->execute(['mailers']);
        $this->assertExitSuccess();
        $this->assertOutputContains(sprintf('<white>[</white> <green>OK</green> <white>] %s Lightning\Test\TestCase\MessageQueue\Command\Task</white', $message1->getId()));
        $this->assertOutputContains(sprintf('<white>[</white> <green>OK</green> <white>] %s Lightning\Test\TestCase\MessageQueue\Command\Task</white', $message2->getId()));
    }

    public function testExecuteWithMessageDeamon(): void
    {
        $message = new Message(serialize(new Task()));
        $this->messageQueue->send('mailers', $message);
        $this->execute(['mailers','-d']);
        $this->assertExitSuccess();
        $this->assertOutputContains(sprintf('<white>[</white> <green>OK</green> <white>] %s Lightning\Test\TestCase\MessageQueue\Command\Task</white', $message->getId()));
    }
}
