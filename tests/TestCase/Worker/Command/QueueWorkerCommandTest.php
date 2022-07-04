<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Worker\Command;

use PDO;
use PHPUnit\Framework\TestCase;

use Lightning\Fixture\FixtureManager;
use Lightning\MessageQueue\MessageConsumer;
use Lightning\MessageQueue\MessageProducer;
use Lightning\Console\ConsoleArgumentParser;
use Lightning\Console\TestSuite\TestConsoleIo;
use Lightning\MessageQueue\MemoryMessageQueue;
use Lightning\Worker\Command\QueueWorkerCommand;
use Lightning\MessageQueue\MessageQueueInterface;
use Lightning\Console\TestSuite\ConsoleIntegrationTestTrait;

class Message
{
    public function __construct(protected string $body)
    {
    }

    public function getBody(): string
    {
        return $this->body;
    }
}

class MessageListener
{
    public function __construct(protected TestCase $testCase)
    {
    }
    public function __invoke(object $message)
    {
        $this->testCase->assertTrue(true);
    }
}

final class QueueWorkerCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected PDO $pdo;
    protected string $migrationFolder;

    protected FixtureManager $fixtureManager;

    protected MessageQueueInterface $messageQueue;
    protected MessageConsumer $messageConsumer;

    public function setUp(): void
    {
        $this->messageQueue = new MemoryMessageQueue();
        $this->messageConsumer = new MessageConsumer($this->messageQueue, 'default');

        $command = new QueueWorkerCommand(
            new ConsoleArgumentParser(),
            new TestConsoleIo(),
            $this->messageConsumer
        );

        $this->setupIntegrationTesting($command);

        $this->messageConsumer->setMessageListener(new MessageListener($this));
    }

    public function testExecute(): void
    {
        $this->execute(['default']);
        $this->assertExitSuccess();
    }

    public function testExecuteWithMessage(): void
    {
        $message = new Message('foo');
        (new MessageProducer($this->messageQueue))->send('mailers', $message);
        $this->execute(['mailers']);
        $this->assertExitSuccess();
    }

    public function testExecuteWithMessages(): void
    {
        $message1 = new Message('foo');
        $message2 = new Message('bar');
        (new MessageProducer($this->messageQueue))->send('mailers', $message1);
        (new MessageProducer($this->messageQueue))->send('mailers', $message2);
        $this->execute(['mailers']);
        $this->assertExitSuccess();
    }
}
