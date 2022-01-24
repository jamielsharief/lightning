<?php declare(strict_types=1);

namespace Lightning\Test\Console;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\Console\ConsoleIo;
use Lightning\Console\TestSuite\TestConsoleIo;

final class ConsoleIoTest extends TestCase
{
    public function testSetOutput(): void
    {
        $io = new TestConsoleIo();
        $io->setOutputLevel(ConsoleIo::QUIET);
        $io->out('test', 0);
        $this->assertEquals('', $io->getStdout());
        $io->out('test', 0, ConsoleIo::QUIET);
        $this->assertEquals('test', $io->getStdout());
    }

    public function testSetOutputException(): void
    {
        $io = new TestConsoleIo();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid output level 100');
        $io->setOutputLevel(100);
    }

    public function testSetOutputMode(): void
    {
        $io = new TestConsoleIo();
        $io->setOutputMode(ConsoleIo::PLAIN);
        $io->out('<white>test<white>', 0);

        $this->assertEquals('test', $io->getStdout());
    }

    public function testSetOutputModeException(): void
    {
        $io = new TestConsoleIo();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid output mode 100');
        $io->setOutputMode(100);
    }

    public function testSetStyle(): void
    {
        $io = new TestConsoleIo();
        $io->setOutputMode(ConsoleIO::COLOR);
        $io->setStyle('test', [
            'background' => 'red',
            'color' => 'white',
            'bold' => true,
            'italic' => true,
            'underline' => true
        ]);

        $io->out('<test>foo</test>', 0);

        $this->assertStringContainsString(
            '0;97;41;1;3;4', $io->getStdout()
        );
    }

    public function testSetStyleInvalidBackgroundColor(): void
    {
        $io = new TestConsoleIo();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid background color `pink`');
        $io->setStyle('test', [
            'background' => 'pink',
        ]);
    }

    public function testSetStyleInvalidColor(): void
    {
        $io = new TestConsoleIo();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid color `pink`');
        $io->setStyle('test', [
            'color' => 'pink',
        ]);
    }

    public function testOut(): void
    {
        $io = new TestConsoleIo();
        $io->out('foo');
        $this->assertEquals(
            "foo\n", $io->getStdout()
        );
    }

    public function testOutArray(): void
    {
        $io = new TestConsoleIo();
        $io->out(['foo','bar']);
        $this->assertEquals(
            "foo\nbar\n", $io->getStdout()
        );
    }

    public function testOutNoNewLine(): void
    {
        $io = new TestConsoleIo();
        $io->out('foo', 0);
        $this->assertEquals(
            'foo', $io->getStdout()
        );
    }

    public function testOutMultiple(): void
    {
        $io = new TestConsoleIo();
        $io->out('foo')->out('bar');
        $this->assertEquals(
            "foo\nbar\n", $io->getStdout()
        );
    }

    public function testOutOutputMode(): void
    {
        $io = new TestConsoleIo();
        $io->setOutputLevel(ConsoleIo::QUIET);
        $io->out('foo');
        $io->out('bar', 0, ConsoleIo::QUIET);

        $this->assertEquals(
            'bar', $io->getStdout()
        );
    }

    public function testErr(): void
    {
        $io = new TestConsoleIo();
        $io->err('foo');
        $this->assertEquals(
            "foo\n", $io->getStderr()
        );
    }

    public function testErrArray(): void
    {
        $io = new TestConsoleIo();
        $io->err(['foo','bar']);
        $this->assertEquals(
            "foo\nbar\n", $io->getStderr()
        );
    }

    public function testErrNoNewLine(): void
    {
        $io = new TestConsoleIo();
        $io->err('foo', 0);
        $this->assertEquals(
            'foo', $io->getStderr()
        );
    }

    public function testErrMultiple(): void
    {
        $io = new TestConsoleIo();
        $io->err('foo')->err('bar');
        $this->assertEquals(
            "foo\nbar\n", $io->getStderr()
        );
    }

    public function testOutWrite(): void
    {
        $out = fopen('php://temp', 'r+');
        $other = fopen('php://temp', 'r+');

        $io = new ConsoleIo($out, $other, $other);

        $io->out('foo', 0);
        $io->err('bar', 0);

        rewind($out);

        $this->assertEquals('foo', stream_get_contents($out));

        fclose($out);
        fclose($other);
    }

    public function testErrWrite(): void
    {
        $out = fopen('php://temp', 'r+');
        $other = fopen('php://temp', 'r+');

        $io = new ConsoleIo($other, $out, $other);

        $io->out('foo', 0);
        $io->err('bar', 0);

        rewind($out);

        $this->assertEquals('bar', stream_get_contents($out));

        fclose($out);
        fclose($other);
    }

    public function testIn(): void
    {
        $stdout = fopen('php://temp', 'r+');
        $stderr = fopen('php://temp', 'r+');
        $stdin = fopen('php://temp', 'r+');
        $io = new ConsoleIo($stdout, $stderr, $stdin);

        $this->assertEquals('default', $io->in('default'));

        fputs($stdin, 'foo');
        rewind($stdin);

        $this->assertEquals('foo', $io->in());

        fclose($stdout);
        fclose($stderr);
        fclose($stdin);
    }

    public function testAsk(): void
    {
        $io = new TestConsoleIo();
        $io->setInput(['']);
        $io->ask('What is your name?');
        $this->assertEquals("What is your name?\n<white>></white> ", $io->getStdout());
    }

    public function testAskWithDefault(): void
    {
        $io = new TestConsoleIo();
        $io->setInput(['jimbo']);

        $io->ask('Do you want to continue?', 'n');
        $this->assertEquals("Do you want to continue? [n]\n<white>></white> ", $io->getStdout());
    }

    public function testAskChoices(): void
    {
        $io = new TestConsoleIo();
        $io->setInput(['-','y']);;

        $this->assertEquals('y', $io->askChoice('Do you want to continue? (y/n)', ['y','n']));
    }

    public function testNl(): void
    {
        $io = new TestConsoleIo();
        $io->nl();
        $this->assertEquals("\n", $io->getStdout());

        $io->reset();
        $io->nl(3);
        $this->assertEquals("\n\n\n", $io->getStdout());
    }

    public function testHr(): void
    {
        $io = new TestConsoleIo();
        $io->hr();

        $this->assertEquals(str_repeat('-', 80) . "\n", $io->getStdout());
    }

    public function testInfo(): void
    {
        $io = new TestConsoleIo();
        $io->setOutputMode(ConsoleIo::COLOR);
        $io->info('INFO', 'Hello world');

        $this->assertEquals(
            "\033[0;97;44;1m INFO \033[0m Hello world\n",
            $io->getStdout()
        );
    }

    public function testSuccess(): void
    {
        $io = new TestConsoleIo();
        $io->setOutputMode(ConsoleIo::COLOR);
        $io->success('SUCCESS', 'Hello world');

        $this->assertEquals(
            "\033[0;97;42;1m SUCCESS \033[0m Hello world\n",
            $io->getStdout()
        );
    }

    public function testWarning(): void
    {
        $io = new TestConsoleIo();
        $io->setOutputMode(ConsoleIo::COLOR);
        $io->warning('WARNING', 'Hello world');

        $this->assertEquals(
            "\033[0;97;43;1m WARNING \033[0m Hello world\n",
            $io->getStderr()
        );
    }

    public function testError(): void
    {
        $io = new TestConsoleIo();
        $io->setOutputMode(ConsoleIo::COLOR);
        $io->error('ERROR', 'Hello world');

        $this->assertEquals(
            "\033[0;97;41;1m ERROR \033[0m Hello world\n",
            $io->getStderr()
        );
    }

    public function testStatus(): void
    {
        $io = new TestConsoleIo();
        $io->status('ok', 'it worked');
        $this->assertEquals("<white>[</white> <green>OK</green> <white>] it worked</white>\n", $io->getStdout());

        $io = new TestConsoleIo();
        $io->status('warning', 'it worked');
        $this->assertEquals("<white>[</white> <yellow>WARNING</yellow> <white>] it worked</white>\n", $io->getStdout());

        $io = new TestConsoleIo();
        $io->status('error', 'it worked');
        $this->assertEquals("<white>[</white> <red>ERROR</red> <white>] it worked</white>\n", $io->getStdout());
    }

    public function testStatusException(): void
    {
        $io = new TestConsoleIo();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unkown status `foo`');
        $io->status('foo', 'bar');
    }

    public function testSetStatus(): void
    {
        $io = new TestConsoleIo();

        $this->assertInstanceOf(TestConsoleIo::class, $io->setStatus('foo', 'blue'));
        $io->status('foo', 'bar');
        $this->assertEquals("<white>[</white> <blue>FOO</blue> <white>] bar</white>\n", $io->getStdout());
    }

    public function testProgressBar(): void
    {
        $io = new TestConsoleIo();
        $io->setOutputMode(ConsoleIo::COLOR);
        $io->progressBar(50, 100);
        $this->assertEquals(
            "\r[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;34;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m[0;90;49m█[0m [0;34;49m 50%[0m",
            $io->getStdout()
        );
    }

    public function testProgressBarNoColor(): void
    {
        $io = new TestConsoleIo();
        $io->setOutputMode(ConsoleIo::PLAIN);
        $io->progressBar(50, 100);
        $this->assertEquals(
            "\r█████████████████████████                          [  50% ]",
            $io->getStdout()
        );
    }
}
