<?php declare(strict_types=1);

namespace Lightning\Test\Console;

use PHPUnit\Framework\TestCase;
use Lightning\Console\Arguments;
use Lightning\Console\ConsoleIo;
use Lightning\Console\AbstractCommand;
use Lightning\Console\ConsoleArgumentParser;
use Lightning\Console\Exception\StopException;
use Lightning\Console\TestSuite\TestConsoleIo;

class HelloCommand extends AbstractCommand
{
    protected string $name = 'hello';
    protected string $description = 'hello world';

    protected function initialize(): void
    {
        $this->addArgument('name', ['description' => 'name to use', 'default' => 'world']);
        $this->addOption('uppercase', ['description' => 'change name to uppercase', 'short' => 'u']);
        $this->addOption('abort', [
            'type' => 'boolean'
        ]);
    }

    protected function execute(Arguments $args, ConsoleIo $io)
    {
        if ($args->getOption('abort')) {
            $this->abort();
        }

        // test Add argument and addOption
        $name = $args->getArgument('name');
        if ($args->getOption('u')) {
            $name = strtoupper($name);
        }

        $this->out(sprintf('Hello %s', $args->getArgument('name')));
    }
}

final class AbstractCommandTest extends TestCase
{
    public function testGetConsoleIo(): void
    {
        $command = new HelloCommand(new ConsoleArgumentParser(), new TestConsoleIo());
        $this->assertInstanceOf(ConsoleIo::class, $command->getConsoleIo());
    }

    public function testGetName(): void
    {
        $command = new HelloCommand(new ConsoleArgumentParser(), new TestConsoleIo());
        $this->assertEquals('hello', $command->getName());
    }

    public function testGetDescription(): void
    {
        $command = new HelloCommand(new ConsoleArgumentParser(), new TestConsoleIo());
        $this->assertEquals('hello world', $command->getDescription());
    }

    public function testAddOption(): void
    {
        $parser = new ConsoleArgumentParser();
        $command = new HelloCommand($parser, new TestConsoleIo());
        $command->addOption('uppercase', ['description' => 'change name to uppercase', 'short' => 'u']);

        $this->assertEquals(
            'change name to uppercase',
            $parser->generateOptions()['-u,--uppercase']
        );
    }

    public function testAddArgument(): void
    {
        $parser = new ConsoleArgumentParser();
        $command = new HelloCommand($parser, new TestConsoleIo());
        $command->addArgument('name', ['description' => 'name to use', 'default' => 'world']);

        $this->assertEquals(
            'name to use (default: "world")',
            $parser->generateArguments()['name']
        );
    }

    public function testExit(): void
    {
        $command = new HelloCommand(new ConsoleArgumentParser(), new TestConsoleIo());

        $this->expectException(StopException::class);
        $this->expectExceptionMessage('Command exited');
        $this->expectExceptionCode(AbstractCommand::SUCCESS);

        $command->exit();
    }

    public function testAbort(): void
    {
        $command = new HelloCommand(new ConsoleArgumentParser(), new TestConsoleIo());

        $this->expectException(StopException::class);
        $this->expectExceptionMessage('Command aborted');
        $this->expectExceptionCode(AbstractCommand::ERROR);

        $command->abort();
    }

    public function testRun(): void
    {
        $stub = new TestConsoleIo();
        $command = new HelloCommand(new ConsoleArgumentParser(), $stub);

        $this->assertEquals(AbstractCommand::SUCCESS, $command->run(['bin/console']));
        $this->assertStringContainsString('Hello world', $stub->getStdout());
    }

    public function testRunCatchStopException(): void
    {
        $stub = new TestConsoleIo();
        $command = new HelloCommand(new ConsoleArgumentParser(), $stub);

        $this->assertEquals(AbstractCommand::ERROR, $command->run(['bin/console','--abort']));
    }

    public function testOut(): void
    {
        $stub = new TestConsoleIo();
        $command = new HelloCommand(new ConsoleArgumentParser(), $stub);
        $command->out('foo');
        $this->assertStringContainsString('foo', $stub->getStdout());
    }

    public function testError(): void
    {
        $stub = new TestConsoleIo();
        $command = new HelloCommand(new ConsoleArgumentParser(), $stub);
        $command->error('foo');
        $this->assertStringContainsString('foo', $stub->getStderr());
    }

    public function testVerbose(): void
    {
        $stub = new TestConsoleIo();
        $command = new HelloCommand(new ConsoleArgumentParser(), $stub);
        $command->verbose('foo');
        $this->assertEmpty($stub->getStdout());

        $stub->setOutputLevel(ConsoleIo::VERBOSE);
        $command->verbose('foo');

        $this->assertStringContainsString('foo', $stub->getStdout());
    }

    public function testQuiet(): void
    {
        $stub = new TestConsoleIo();
        $command = new HelloCommand(new ConsoleArgumentParser(), $stub);
        $stub->setOutputLevel(ConsoleIo::QUIET);
        $command->out('normal');
        $command->quiet('foo');

        $this->assertStringNotContainsString('normal', $stub->getStdout());
        $this->assertStringContainsString('foo', $stub->getStdout());
    }

    public function testDisplayHelp(): void
    {
        $stub = new TestConsoleIo();
        $command = new HelloCommand(new ConsoleArgumentParser(), $stub);
        $command->run(['bin/console', '-h']);

        $expected = "hello world\n\n<yellow>Usage:</yellow>\n  hello [options] [name]\n\n<yellow>Arguments:</yellow>\n  <green>name           </green>name to use (default: \"world\")\n\n<yellow>Options:</yellow>\n  <green>-h,--help      </green>Displays this help message\n  <green>-v,--verbose   </green>Displays additional output (if available)\n  <green>-q,--quiet     </green>Does not display output\n  <green>-u,--uppercase </green>change name to uppercase\n  <green>--abort        </green>\n\n";

        $this->assertEquals($expected, $stub->getStdout());
    }

    public function testThrowError(): void
    {
        $stub = new TestConsoleIo();
        $command = new HelloCommand(new ConsoleArgumentParser(), $stub);
        $stub->setOutputMode(ConsoleIo::RAW);
        $this->expectException(StopException::class);
        $this->expectExceptionMessage('Opps error');

        try {
            $command->throwError('Opps error', 'message');
        } catch (StopException $exception) {
            $this->assertStringContainsString("<alert> ERROR </alert> <lightYellow>Opps error</lightYellow>\nmessage\n", $stub->getStderr());

            throw $exception;
        }
    }
}
