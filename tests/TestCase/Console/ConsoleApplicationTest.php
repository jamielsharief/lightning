<?php declare(strict_types=1);

namespace Lightning\Test\Console;

use PHPUnit\Framework\TestCase;
use Lightning\Console\Arguments;
use Lightning\Console\ConsoleIo;
use Lightning\Console\AbstractCommand;
use Lightning\Console\ConsoleApplication;
use Lightning\Console\ConsoleArgumentParser;
use Lightning\Console\TestSuite\TestConsoleIo;

class FooCommand extends AbstractCommand
{
    protected string $name = 'foo';
    protected string $description = 'foo command';
    protected function initialize(): void
    {
        $this->addArgument('name', [
            'description' => 'name to use'
        ]);
    }
    protected function execute(Arguments $args, ConsoleIo $io)
    {
        $this->out('foo:' .  $args->getArgument('name', 'none'));
    }
}

class BarCommand extends AbstractCommand
{
    protected string $name = 'bar';
    protected string $description = 'bar command';
    protected function execute(Arguments $args, ConsoleIo $io)
    {
        $this->out('bar');
    }
}

final class ConsoleApplicationTest extends TestCase
{
    public function testGetName(): void
    {
        $consoleIo = new TestConsoleIo();
        $app = new ConsoleApplication($consoleIo);
        $this->assertEquals('unkown', $app->getName());
    }

    /**
     * @depends testGetName
     */
    public function testSetName(): void
    {
        $consoleIo = new TestConsoleIo();
        $app = new ConsoleApplication($consoleIo);
        $this->assertInstanceOf(ConsoleApplication::class, $app->setName('foo'));

        $this->assertEquals('foo', $app->getName());
    }

    public function testGetDescription(): void
    {
        $consoleIo = new TestConsoleIo();
        $app = new ConsoleApplication($consoleIo);
        $this->assertEquals('', $app->getDescription());
    }

    /**
     * @depends testGetDescription
     */
    public function testSetDescription(): void
    {
        $consoleIo = new TestConsoleIo();
        $app = new ConsoleApplication($consoleIo);
        $this->assertInstanceOf(ConsoleApplication::class, $app->setDescription('foo'));

        $this->assertEquals('foo', $app->getDescription());
    }

    public function testAdd(): void
    {
        $consoleIo = new TestConsoleIo();
        $app = new ConsoleApplication($consoleIo);
        $this->assertInstanceOf(ConsoleApplication::class, $app->add(new FooCommand(new ConsoleArgumentParser(), $consoleIo)));
    }

    /**
        * @depends testAdd
        */
    public function testDisplayHelp(): void
    {
        $consoleIo = new TestConsoleIo();
        $app = new ConsoleApplication($consoleIo);
        $app->add(new FooCommand(new ConsoleArgumentParser(), $consoleIo));
        $app->add(new BarCommand(new ConsoleArgumentParser(), $consoleIo));
        $this->assertEquals(0, $app->run(['bin/foo']));

        $this->assertEquals("<yellow>Usage:</yellow>\n  unkown <command> [options] [arguments]\n\n<yellow>Commands:</yellow>\n  <green>foo     </green>foo command\n  <green>bar     </green>bar command\n\n", $consoleIo->getStdout());
    }

    /**
     * @depends testAdd
     */
    public function testRun(): void
    {
        $consoleIo = new TestConsoleIo();
        $app = new ConsoleApplication($consoleIo);
        $app->add(new FooCommand(new ConsoleArgumentParser(), $consoleIo));
        $this->assertEquals(0, $app->run(['bin/foo','foo']));
        $this->assertStringContainsString('foo:none', $consoleIo->getStdout());
    }

    /**
    * @depends testAdd
    */
    public function testRunWithArgs(): void
    {
        $consoleIo = new TestConsoleIo();
        $app = new ConsoleApplication($consoleIo);
        $app->add(new FooCommand(new ConsoleArgumentParser(), $consoleIo));
        $this->assertEquals(0, $app->run(['bin/foo','foo','bar']));
        $this->assertStringContainsString('foo:bar', $consoleIo->getStdout());
    }
}
