<?php declare(strict_types=1);

namespace Lightning\Test\Console;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use Lightning\Console\Arguments;
use Lightning\Console\ConsoleIo;
use Lightning\ServiceObject\Result;
use Lightning\Console\AbstractCommand;
use Lightning\Console\ConsoleArgumentParser;
use Lightning\Console\TestSuite\ConsoleIoStub;
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
        $this->expectExceptionMessage('Console IO stub not created');
        $this->getIo();
    }

    public function testCreateConsoleIoStub(): void
    {
        $this->assertInstanceOf(ConsoleIoStub::class, $this->createConsoleIoStub());
    }

    /**
     * @depends testCreateConsoleIoStub
     */
    public function testGetConsoleIoStub(): void
    {
        $this->createConsoleIoStub();
        $this->assertInstanceOf(ConsoleIoStub::class, $this->getIo());
    }

    public function testExitSuccesss(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->assertTrue($this->execute($command));
        $this->assertExitSuccess();
    }

    public function testExitError(): void
    {
        $command = $this->createCommand(DummyCommand::class, new Result());

        $this->assertFalse($this->execute($command, ['--abort']));
        $this->assertExitError();
    }

    public function testExitCode(): void
    {
        $command = $this->createCommand(DummyCommand::class);

        $this->execute($command, ['--abort']);

        $this->assertExitCode(3);
    }

    public function testOutputContains(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->assertOutputContains('hello world');
    }

    /**
     * @depends testOutputContains
     */
    public function testOutputNotContains(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->getIo()->reset();

        $this->assertOutputNotContains('hello world');
    }

    public function testOutputEmpty(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->getIo()->reset();

        $this->assertOutputEmpty();
    }

    public function testOutputNotEmpty(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->assertOutputNotEmpty();
    }

    public function testOutputMatchesRegularExpression(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->assertOutputMatchesRegularExpression('/hello world/');
    }

    public function testOutputDoesNotMatchRegularExpression(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->getIo()->reset();

        $this->assertOutputDoesNotMatchRegularExpression('/hello world/');
    }

    public function testErrorContains(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->assertErrorContains('an error has occured');
    }

    /**
     * @depends testErrorContains
     */
    public function testErrorNotContains(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->getIo()->reset();

        $this->assertErrorNotContains('an error has occured');
    }

    public function testErrorEmpty(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->getIo()->reset();

        $this->assertErrorEmpty();
    }

    public function testErrorNotEmpty(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->assertErrorNotEmpty();
    }

    public function testErrorMatchesRegularExpression(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->assertErrorMatchesRegularExpression('/an error has occured/');
    }

    public function testErrorDoesNotMatchRegularExpression(): void
    {
        $command = $this->createCommand(DummyCommand::class);
        $this->execute($command);

        $this->getIo()->reset();

        $this->assertErrorDoesNotMatchRegularExpression('/an error has occured/');
    }
}
