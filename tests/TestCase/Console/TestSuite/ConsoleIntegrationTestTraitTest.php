<?php declare(strict_types=1);

namespace Lightning\Test\Console;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use Lightning\Console\Arguments;
use Lightning\Console\ConsoleIo;
use Lightning\ServiceObject\Result;
use Lightning\Console\AbstractCommand;
use Lightning\Console\ConsoleArgumentParser;
use Lightning\Console\TestSuite\TestConsoleIo;
use Lightning\Console\TestSuite\ConsoleIntegrationTestTrait;

class DummyCommand extends AbstractCommand
{
    protected string $name = 'hello';
    protected string $description = 'hello world';

    public function __construct(ConsoleArgumentParser $parser, ConsoleIo $io, ?object $object = null)
    {
        parent::__construct($parser, $io);

        if ($object) {
            $this->out('object:' . get_class($object));
        }
    }
    protected function initialize(): void
    {
        $this->addOption('display', [
            'default' => 'hello world',
            'type' => 'string'
        ]);

        $this->addOption('abort', [
            'type' => 'boolean'
        ]);
    }

    protected function execute(Arguments $args, ConsoleIo $io)
    {
        if ($args->hasOption('display')) {
            $this->out($args->getOption('display'));
        }

        if ($args->getOption('abort')) {
            $this->abort(3);
        }

        $this->out('Hello world');
        $this->error('an error has occured');

        return self::SUCCESS;
    }
}

final class ConsoleIntegrationTestTraitTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    public function testGetIoException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Console IO stub not set');
        $this->getConsoleIo();
    }

    public function testCreateTestConsoleIo(): void
    {
        $this->assertInstanceOf(TestConsoleIo::class, $this->createConsoleIo());
    }

    /**
     * @depends testCreateTestConsoleIo
     */
    public function testGetTestConsoleIo(): void
    {
        $this->setConsoleIo(new TestConsoleIo());
        $this->assertInstanceOf(TestConsoleIo::class, $this->getConsoleIo());
    }

    public function testExitSuccesss(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo());
        $this->setupIntegrationTesting($command);

        $this->assertTrue($this->execute());
        $this->assertExitSuccess();
    }

    public function testExitError(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->assertFalse($this->execute(['--abort']));
        $this->assertExitError();
    }

    public function testExitCode(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute(['--abort']);

        $this->assertExitCode(3);
    }

    public function testOutputContains(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->assertOutputContains('hello world');
    }

    /**
     * @depends testOutputContains
     */
    public function testOutputNotContains(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->getConsoleIo()->reset();

        $this->assertOutputNotContains('hello world');
    }

    public function testOutputEmpty(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->getConsoleIo()->reset();

        $this->assertOutputEmpty();
    }

    public function testOutputNotEmpty(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->assertOutputNotEmpty();
    }

    public function testOutputMatchesRegularExpression(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->assertOutputMatchesRegularExpression('/hello world/');
    }

    public function testOutputDoesNotMatchRegularExpression(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->getConsoleIo()->reset();

        $this->assertOutputDoesNotMatchRegularExpression('/hello world/');
    }

    public function testErrorContains(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->assertErrorContains('an error has occured');
    }

    /**
     * @depends testErrorContains
     */
    public function testErrorNotContains(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->getConsoleIo()->reset();

        $this->assertErrorNotContains('an error has occured');
    }

    public function testErrorEmpty(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->getConsoleIo()->reset();

        $this->assertErrorEmpty();
    }

    public function testErrorNotEmpty(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->assertErrorNotEmpty();
    }

    public function testErrorMatchesRegularExpression(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->assertErrorMatchesRegularExpression('/an error has occured/');
    }

    public function testErrorDoesNotMatchRegularExpression(): void
    {
        $command = new DummyCommand(new ConsoleArgumentParser(), new TestConsoleIo(), new Result());
        $this->setupIntegrationTesting($command);

        $this->execute();

        $this->getConsoleIo()->reset();

        $this->assertErrorDoesNotMatchRegularExpression('/an error has occured/');
    }
}
